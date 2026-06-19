<?php

namespace App\Http\Controllers;

use App\Http\Requests\MijozRequest;
use App\Models\Filial;
use App\Models\Mijoz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return view('mijozlar.create', compact('filiallar'));
    }

    /** Mijozni saqlash */
    public function store(MijozRequest $request)
    {
        $mijoz = Mijoz::create($request->validated());

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

        return view('mijozlar.edit', compact('mijoz', 'filiallar'));
    }

    /** Mijozni yangilash */
    public function update(MijozRequest $request, Mijoz $mijoz)
    {
        $this->filialRuxsatTekshir($mijoz->filial_id);

        $mijoz->update($request->validated());

        return redirect()
            ->route('mijozlar.show', $mijoz)
            ->with('muvaffaqiyat', 'Mijoz ma\'lumotlari yangilandi.');
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
                          'telefon','passport_seriya','passport_raqam','holat'])
                ->orderByDesc('created_at');

            $total   = $base->count();
            $pages   = max(1, (int)ceil($total / $perPage));
            $page    = min($page, $pages);
            $mijozlar = (clone $base)->offset(($page - 1) * $perPage)->limit($perPage)->get();

            return response()->json([
                'data'  => $mijozlar->map(fn($m) => [
                    'id'       => $m->id,
                    'fio'      => trim($m->familiya . ' ' . $m->ism .
                                  ($m->otasining_ismi ? ' ' . $m->otasining_ismi : '')),
                    'telefon'  => $m->telefon ?? '',
                    'passport' => trim(($m->passport_seriya ?? '') . ' ' . ($m->passport_raqam ?? '')),
                    'filial'   => $m->filial?->nomi ?? '',
                    'holat'    => $m->holat,
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
                      'telefon','passport_seriya','passport_raqam','holat'])
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        $mapped = $mijozlar->map(fn($m) => [
            'id'       => $m->id,
            'fio'      => trim($m->familiya . ' ' . $m->ism .
                          ($m->otasining_ismi ? ' ' . $m->otasining_ismi : '')),
            'telefon'  => $m->telefon ?? '',
            'passport' => trim(($m->passport_seriya ?? '') . ' ' . ($m->passport_raqam ?? '')),
            'filial'   => $m->filial?->nomi ?? '',
            'holat'    => $m->holat,
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
