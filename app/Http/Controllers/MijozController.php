<?php

namespace App\Http\Controllers;

use App\Http\Requests\MijozRequest;
use App\Models\Filial;
use App\Models\Mijoz;
use App\Models\Tuman;
use App\Models\Viloyat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MijozController extends Controller
{
    /** Mijozlar ro'yxati */
    public function index(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin()
            ? ($request->filial_id ?: null)
            : $user->filial_id;

        $query = Mijoz::with('filial')
            ->withCount('kreditlar')
            ->withSum(['kreditlar as jami_qoldiq_qarz' => fn($q) => $q->where('holat', '!=', 'yopilgan')], 'qoldiq_qarz')
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->when($request->holat, fn($q) => $q->where('holat', $request->holat))
            ->when($request->qidiruv, fn($q) => $q->qidirish($request->qidiruv));

        // Ajax qidiruv uchun (kredit formda mijoz tanlash)
        if ($request->expectsJson()) {
            $mijozlar = $query->faol()
                ->orderBy('familiya')
                ->limit(20)
                ->get(['id', 'familiya', 'ism', 'telefon', 'passport_seriya', 'passport_raqam']);

            return response()->json($mijozlar->map(fn($m) => [
                'id'    => $m->id,
                'nomi'  => $m->familiya . ' ' . $m->ism,
                'tel'   => $m->telefon,
                'passport' => $m->passport_seriya . ' ' . $m->passport_raqam,
            ]));
        }

        $mijozlar = $query->orderBy('familiya')->paginate(25)->withQueryString();
        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();

        return view('mijozlar.index', compact('mijozlar', 'filiallar', 'filialId'));
    }

    /** Mijoz yaratish formasi */
    public function create()
    {
        $user      = Auth::user();
        $filiallar = $user->isAdmin()
            ? Filial::faol()->get()
            : Filial::where('id', $user->filial_id)->get();

        $viloyatlar = Viloyat::royhati();
        $tumanlar   = Tuman::orderBy('sort_order')->get(['id', 'viloyat_id', 'nomi']);
        [$ishJoyilar, $lavozimlar] = $this->ishJoyiVaLavozimRoyxati();

        return view('mijozlar.create', compact('filiallar', 'viloyatlar', 'tumanlar', 'ishJoyilar', 'lavozimlar'));
    }

    /** Mijozni saqlash */
    public function store(MijozRequest $request)
    {
        $data = $request->validated();
        $telefonlar = $data['telefonlar'] ?? [];
        $kartalar   = $data['kartalar'] ?? [];
        unset($data['telefonlar'], $data['kartalar']);

        if ($request->hasFile('rasm')) {
            $data['rasm'] = $this->rasmSaqla($request->file('rasm'));
        } else {
            unset($data['rasm']);
        }

        $mijoz = Mijoz::create($data);
        $this->telefonlarSinxron($mijoz, $telefonlar);
        $this->kartalarSinxron($mijoz, $kartalar);

        return redirect()
            ->route('mijozlar.show', $mijoz)
            ->with('muvaffaqiyat', "Mijoz muvaffaqiyatli qo'shildi.");
    }

    /** Mijoz kartochkasi */
    public function show(Mijoz $mijoz)
    {
        $this->filialRuxsatTekshir($mijoz->filial_id);

        $mijoz->load([
            'filial',
            'viloyat',
            'tuman',
            'telefonlar',
            'kartalar',
            'kreditlar' => fn($q) => $q->with('xodim')->orderByDesc('created_at'),
        ]);

        return view('mijozlar.show', compact('mijoz'));
    }

    /** Mijoz tahrirlash formasi */
    public function edit(Mijoz $mijoz)
    {
        $this->filialRuxsatTekshir($mijoz->filial_id);

        $user      = Auth::user();
        $filiallar = $user->isAdmin()
            ? Filial::faol()->get()
            : Filial::where('id', $user->filial_id)->get();

        $mijoz->load('telefonlar', 'kartalar');

        $viloyatlar = Viloyat::royhati();
        $tumanlar   = Tuman::orderBy('sort_order')->get(['id', 'viloyat_id', 'nomi']);
        [$ishJoyilar, $lavozimlar] = $this->ishJoyiVaLavozimRoyxati();

        return view('mijozlar.edit', compact('mijoz', 'filiallar', 'viloyatlar', 'tumanlar', 'ishJoyilar', 'lavozimlar'));
    }

    /** Mijozni yangilash */
    public function update(MijozRequest $request, Mijoz $mijoz)
    {
        $this->filialRuxsatTekshir($mijoz->filial_id);

        $data = $request->validated();
        $telefonlar = $data['telefonlar'] ?? [];
        $kartalar   = $data['kartalar'] ?? [];
        unset($data['telefonlar'], $data['kartalar']);

        if ($request->hasFile('rasm')) {
            $data['rasm'] = $this->rasmSaqla($request->file('rasm'), $mijoz->rasm);
        } elseif ($request->boolean('rasm_ochir')) {
            if ($mijoz->rasm && Storage::disk('public')->exists($mijoz->rasm)) {
                Storage::disk('public')->delete($mijoz->rasm);
            }
            $data['rasm'] = null;
        } else {
            unset($data['rasm']);
        }

        $mijoz->update($data);
        $this->telefonlarSinxron($mijoz, $telefonlar);
        $this->kartalarSinxron($mijoz, $kartalar);

        return redirect()
            ->route('mijozlar.show', $mijoz)
            ->with('muvaffaqiyat', 'Mijoz ma\'lumotlari yangilandi.');
    }

    /**
     * Boshqa mijozlarda oldin kiritilgan "Ish joyi" va "Lavozimi" qiymatlarini
     * guruhlab (katta-kichik harf farqini hisobga olmay) dublikatlarsiz ro'yxat qilib qaytaradi —
     * forma maydonida tanlov (datalist) sifatida ko'rsatish uchun. Eng ko'p uchraganlari birinchi bo'ladi.
     */
    private function ishJoyiVaLavozimRoyxati(): array
    {
        $ishJoyilar = Mijoz::whereNotNull('ish_joyi')->where('ish_joyi', '!=', '')
            ->whereRaw("ish_joyi NOT REGEXP '^[0-9]+$'")
            ->pluck('ish_joyi');

        $lavozimlar = Mijoz::whereNotNull('lavozimi')->where('lavozimi', '!=', '')
            ->whereRaw("lavozimi NOT REGEXP '^[0-9]+$'")
            ->pluck('lavozimi');

        return [
            $this->guruhlabDedup($ishJoyilar),
            $this->guruhlabDedup($lavozimlar),
        ];
    }

    /** Katta-kichik harfga qaramay guruhlab, chastotasi bo'yicha saralab qiymatlar ro'yxatini qaytaradi */
    private function guruhlabDedup($qiymatlar): array
    {
        $guruhlangan = [];
        foreach ($qiymatlar as $qiymat) {
            $qiymat = trim($qiymat);
            if ($qiymat === '') continue;
            $kalit = mb_strtolower($qiymat);
            if (!isset($guruhlangan[$kalit])) {
                $guruhlangan[$kalit] = ['matn' => $qiymat, 'soni' => 0];
            }
            $guruhlangan[$kalit]['soni']++;
        }
        usort($guruhlangan, fn($a, $b) => $b['soni'] <=> $a['soni']);

        return array_column($guruhlangan, 'matn');
    }

    /** Mijozning qo'shimcha telefon raqamlarini (3 tagacha) qayta yozish */
    private function telefonlarSinxron(Mijoz $mijoz, array $telefonlar): void
    {
        $mijoz->telefonlar()->delete();
        foreach (array_slice($telefonlar, 0, 3) as $i => $t) {
            if (empty($t['telefon'])) continue;
            $mijoz->telefonlar()->create([
                'telefon'        => $t['telefon'],
                'egasi_ismi'     => $t['egasi_ismi'] ?? null,
                'sms_yuborilsin' => !empty($t['sms_yuborilsin']),
                'tartib'         => $i + 1,
            ]);
        }
    }


    /** Mijozning plastik kartalarini (5 tagacha) qayta yozish — legacy karta_raqami ustuniga birinchisi ko'chiriladi */
    private function kartalarSinxron(Mijoz $mijoz, array $kartalar): void
    {
        $mijoz->kartalar()->delete();
        $tozalar = [];
        foreach (array_slice($kartalar, 0, 5) as $k) {
            if (empty($k['karta_raqami'])) continue;
            $tozalar[] = trim($k['karta_raqami']);
        }
        foreach ($tozalar as $i => $raqam) {
            $mijoz->kartalar()->create(['karta_raqami' => $raqam, 'tartib' => $i + 1]);
        }
        $mijoz->update(['karta_raqami' => $tozalar[0] ?? null]);
    }

    /**
     * Mijoz rasmini saqlaydi: GD orqali max 800px tomonga qisqartirib, sifatli-ammo-yengil
     * JPEG (sifat 78) formatga o'giradi. Eski rasm bo'lsa (tahrirlashda) uni o'chiradi.
     * Qaytadi: storage/app/public ichidagi nisbiy yo'l (masalan "mijozlar/m_xxx.jpg").
     */
    private function rasmSaqla($file, ?string $eskiYol = null): string
    {
        $mime = $file->getMimeType();
        $manba = match (true) {
            str_contains($mime, 'png')  => imagecreatefrompng($file->getRealPath()),
            str_contains($mime, 'webp') => imagecreatefromwebp($file->getRealPath()),
            default                     => imagecreatefromjpeg($file->getRealPath()),
        };

        // EXIF orientatsiyasini to'g'rilash (telefon kamerasidan kelgan suratlar uchun)
        if (function_exists('exif_read_data') && str_contains($mime, 'jpeg')) {
            try {
                $exif = @exif_read_data($file->getRealPath());
                if (!empty($exif['Orientation'])) {
                    $manba = match ($exif['Orientation']) {
                        3       => imagerotate($manba, 180, 0),
                        6       => imagerotate($manba, -90, 0),
                        8       => imagerotate($manba, 90, 0),
                        default => $manba,
                    };
                }
            } catch (\Throwable) {
                // EXIF o'qib bo'lmasa — o'girishsiz davom etamiz
            }
        }

        $kenglik = imagesx($manba);
        $balandlik = imagesy($manba);
        $maxTomon = 800;

        if ($kenglik > $maxTomon || $balandlik > $maxTomon) {
            $nisbat = min($maxTomon / $kenglik, $maxTomon / $balandlik);
            $yangiKenglik = (int) round($kenglik * $nisbat);
            $yangiBalandlik = (int) round($balandlik * $nisbat);
            $qisqargan = imagecreatetruecolor($yangiKenglik, $yangiBalandlik);
            imagecopyresampled($qisqargan, $manba, 0, 0, 0, 0, $yangiKenglik, $yangiBalandlik, $kenglik, $balandlik);
            imagedestroy($manba);
            $manba = $qisqargan;
        }

        $nomFayl = 'mijozlar/' . uniqid('m_') . '.jpg';
        $toliqYol = Storage::disk('public')->path($nomFayl);
        if (!is_dir(dirname($toliqYol))) {
            mkdir(dirname($toliqYol), 0755, true);
        }
        imagejpeg($manba, $toliqYol, 78);
        imagedestroy($manba);

        if ($eskiYol && Storage::disk('public')->exists($eskiYol)) {
            Storage::disk('public')->delete($eskiYol);
        }

        return $nomFayl;
    }

    /** AJAX JSON qidiruv — modal tanlov uchun */
    /** AJAX JSON qidiruv - modal tanlov uchun
     * Lotincha, Kirilcha va telefon raqami bilan qidiruvni qo'llab-quvvatlaydi.
     */
    public function ajaxQidiruv(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin()
            ? ($request->filial_id ?: null)
            : $user->filial_id;

        $q = trim($request->q ?? '');

        $perPage = 20;
        $page    = max(1, (int)($request->page ?? 1));

        // Bo'sh q bo'lsa — sahifalab ko'rsatish
        if (mb_strlen($q) < 2) {
            $base = Mijoz::with('filial:id,nomi,kod')
                ->when($filialId, fn($qu) => $qu->where('filial_id', $filialId))
                ->select(['id','filial_id','familiya','ism','otasining_ismi',
                          'telefon','passport_seriya','passport_raqam','pinfl',
                          'manzil','izoh','karta_raqami','holat'])
                ->orderByDesc('created_at');

            $total   = $base->count();
            $pages   = max(1, (int)ceil($total / $perPage));
            $page    = min($page, $pages);
            $mijozlar = (clone $base)->offset(($page - 1) * $perPage)->limit($perPage)->get();

            return response()->json([
                'data'  => $mijozlar->map(fn($m) => [
                    'id'           => $m->id,
                    'fio'          => trim($m->familiya . ' ' . $m->ism .
                                      ($m->otasining_ismi ? ' ' . $m->otasining_ismi : '')),
                    'telefon'      => $m->telefon ?? '',
                    'passport'     => trim(($m->passport_seriya ?? '') . ' ' . ($m->passport_raqam ?? '')),
                    'pinfl'        => $m->pinfl ?? '',
                    'manzil'       => $m->manzil ?? '',
                    'izoh'         => $m->izoh ?? '',
                    'karta_raqami' => $m->karta_raqami ?? '',
                    'filial'       => $m->filial?->nomi ?? '',
                    'holat'        => $m->holat,
                ]),
                'total' => $total,
                'page'  => $page,
                'pages' => $pages,
            ]);
        }

        // Ikkala alifbo versiyasini tayyorlaymiz
        $qLow   = mb_strtolower($q);
        $qLatin = $this->toLatinUz($q);
        $qCyr   = $this->toCyrillicUz($q);

        $mijozlar = Mijoz::with('filial:id,nomi,kod')
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->where(function($sub) use ($q, $qLow, $qLatin, $qCyr) {

                // Asosiy qidiruv (asl matn)
                $sub->whereRaw('LOWER(familiya) LIKE ?', ["%{$qLow}%"])
                    ->orWhereRaw('LOWER(ism) LIKE ?', ["%{$qLow}%"])
                    ->orWhereRaw("LOWER(CONCAT(familiya,' ',ism)) LIKE ?", ["%{$qLow}%"])
                    ->orWhere('telefon', 'LIKE', "%{$q}%")
                    ->orWhereRaw("REPLACE(telefon,' ','') LIKE ?",
                        ['%' . preg_replace('/\D/', '', $q) . '%'])
                    ->orWhere('passport_seriya', 'LIKE', "%{$q}%")
                    ->orWhere('passport_raqam', 'LIKE', "%{$q}%");

                // Lotincha transliteratsiya (foydalanuvchi kirilcha yozgan bo'lsa)
                if ($qLatin !== $qLow) {
                    $sub->orWhereRaw('LOWER(familiya) LIKE ?', ["%{$qLatin}%"])
                        ->orWhereRaw('LOWER(ism) LIKE ?', ["%{$qLatin}%"])
                        ->orWhereRaw("LOWER(CONCAT(familiya,' ',ism)) LIKE ?", ["%{$qLatin}%"]);
                }

                // Kirilcha transliteratsiya (foydalanuvchi lotincha yozgan bo'lsa)
                if ($qCyr !== $qLow && $qCyr !== $qLatin) {
                    $sub->orWhereRaw('LOWER(familiya) LIKE ?', ["%{$qCyr}%"])
                        ->orWhereRaw('LOWER(ism) LIKE ?', ["%{$qCyr}%"])
                        ->orWhereRaw("LOWER(CONCAT(familiya,' ',ism)) LIKE ?", ["%{$qCyr}%"]);
                }
            })
            ->select(['id','filial_id','familiya','ism','otasining_ismi',
                      'telefon','passport_seriya','passport_raqam','pinfl',
                      'manzil','izoh','karta_raqami','holat'])
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        $mapped = $mijozlar->map(fn($m) => [
            'id'           => $m->id,
            'fio'          => trim($m->familiya . ' ' . $m->ism .
                              ($m->otasining_ismi ? ' ' . $m->otasining_ismi : '')),
            'telefon'      => $m->telefon ?? '',
            'passport'     => trim(($m->passport_seriya ?? '') . ' ' . ($m->passport_raqam ?? '')),
            'pinfl'        => $m->pinfl ?? '',
            'manzil'       => $m->manzil ?? '',
            'izoh'         => $m->izoh ?? '',
            'karta_raqami' => $m->karta_raqami ?? '',
            'filial'       => $m->filial?->nomi ?? '',
            'holat'        => $m->holat,
        ]);
        return response()->json(['data' => $mapped, 'total' => count($mapped), 'page' => 1, 'pages' => 1]);
    }

    /** Kirilchani lotinga o'girish (O'zbek alfaviti) */
    private function toLatinUz(string $s): string
    {
        return strtr(mb_strtolower($s), [
            'ш'=>'sh','щ'=>'sh','ч'=>'ch','ё'=>'yo','ю'=>'yu','я'=>'ya',
            'е'=>'ye','ц'=>'ts','ж'=>'zh','ъ'=>"'",'ь'=>"'",'ы'=>'i',
            'ғ'=>"g'",'қ'=>'q','ҳ'=>'h','ҷ'=>'j','ў'=>"o'",
            'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','з'=>'z',
            'и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n',
            'о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u',
            'ф'=>'f','х'=>'x','э'=>'e',
        ]);
    }

    /** Lotinchani kirilchaga o'girish (O'zbek alfaviti) */
    private function toCyrillicUz(string $s): string
    {
        $s = mb_strtolower($s);
        // Ko'p harfli birikmallar - avval
        foreach (["o'"=>'ў',"g'"=>'ғ','sh'=>'ш','ch'=>'ч','yo'=>'ё',
                  'yu'=>'ю','ya'=>'я','ye'=>'е','ts'=>'ц','zh'=>'ж'] as $l => $c) {
            $s = str_replace($l, $c, $s);
        }
        // Yakkalar
        return strtr($s, [
            'a'=>'а','b'=>'б','d'=>'д','e'=>'э','f'=>'ф','g'=>'г',
            'h'=>'х','i'=>'и','j'=>'ж','k'=>'к','l'=>'л','m'=>'м',
            'n'=>'н','o'=>'о','p'=>'п','q'=>'қ','r'=>'р','s'=>'с',
            't'=>'т','u'=>'у','v'=>'в','x'=>'х','y'=>'й','z'=>'з',
        ]);
    }

    /** Filial ruxsatini tekshirish */
    private function filialRuxsatTekshir(int $mijozFilialId): void
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $user->filial_id !== $mijozFilialId) {
            abort(403, 'Bu mijoz sizning filialingizga tegishli emas.');
        }
    }
}
