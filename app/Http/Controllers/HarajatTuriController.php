<?php

namespace App\Http\Controllers;

use App\Models\Harajat;
use App\Models\HarajatTuri;
use App\Models\PulKategoriya;
use App\Models\Taminotchi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HarajatTuriController extends Controller
{
    /** "Таъминотчилар:" turi — alohida (taminotchiMigratsiya) vositasi orqali boshqariladi, umumiy bog'lash ro'yxatida ko'rsatilmaydi. */
    private const TAMINOTCHI_TURI_MATN = 'таъминотчилар';

    public function index()
    {
        $turlar = HarajatTuri::with('kategoriya.ota')->withCount('harajatlar')
            ->orderBy('sort_order')->orderBy('nomi')->get();

        // Eski (hali bog'lanmagan) erkin matnli "turi" qiymatlari — bular
        // harajat_turi_id = null bo'lgan yozuvlardan kelib chiqadi.
        // "Таъминотчилар:" alohida vositada (pastda) boshqariladi.
        $bogLanmaganlar = Harajat::whereNull('harajat_turi_id')
            ->whereRaw('LOWER(TRIM(turi)) NOT LIKE ?', [self::TAMINOTCHI_TURI_MATN . '%'])
            ->select('turi')
            ->selectRaw('COUNT(*) as soni, SUM(summa) as jami')
            ->groupBy('turi')
            ->orderByDesc('soni')
            ->get();

        $taminotchiSoni = Harajat::whereNull('harajat_turi_id')
            ->whereNull('taminotchi_tulov_id')
            ->whereRaw('LOWER(TRIM(turi)) LIKE ?', [self::TAMINOTCHI_TURI_MATN . '%'])
            ->count();

        $kategoriyalar = PulKategoriya::where('holat', 'faol')
            ->whereDoesntHave('bolalar')->with('ota')
            ->orderBy('yunalish')->orderBy('sort_order')->get();

        return view('malumotnamalar.harajat-turlari.index', compact('turlar', 'bogLanmaganlar', 'taminotchiSoni', 'kategoriyalar'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomi'              => 'required|string|max:255|unique:harajat_turlari,nomi',
            'pul_kategoriya_id' => 'required|exists:pul_kategoriyalar,id',
            'talab_xodim'       => 'nullable|boolean',
            'talab_schetchik'   => 'nullable|boolean',
        ]);

        HarajatTuri::create([
            'nomi'              => trim($request->nomi),
            'pul_kategoriya_id' => $request->pul_kategoriya_id,
            'talab_xodim'       => (bool) $request->talab_xodim,
            'talab_schetchik'   => (bool) $request->talab_schetchik,
            'holat'             => 'faol',
            'sort_order'        => (HarajatTuri::max('sort_order') ?? 0) + 1,
        ]);

        return back()->with('muvaffaqiyat', "Harajat turi qo'shildi.");
    }

    public function update(Request $request, HarajatTuri $harajatTuri)
    {
        $request->validate([
            'nomi'              => 'required|string|max:255|unique:harajat_turlari,nomi,' . $harajatTuri->id,
            'pul_kategoriya_id' => 'required|exists:pul_kategoriyalar,id',
            'talab_xodim'       => 'nullable|boolean',
            'talab_schetchik'   => 'nullable|boolean',
            'holat'             => 'in:faol,nofaol',
        ]);

        $harajatTuri->update([
            'nomi'              => trim($request->nomi),
            'pul_kategoriya_id' => $request->pul_kategoriya_id,
            'talab_xodim'       => (bool) $request->talab_xodim,
            'talab_schetchik'   => (bool) $request->talab_schetchik,
            'holat'             => $request->holat ?? $harajatTuri->holat,
        ]);

        return back()->with('muvaffaqiyat', "Harajat turi yangilandi.");
    }

    public function destroy(HarajatTuri $harajatTuri)
    {
        if ($harajatTuri->harajatlar()->exists()) {
            return back()->with('xato', "Bu turdan {$harajatTuri->harajatlar()->count()} ta harajatda foydalanilgan — o'chirib bo'lmaydi, \"Nofaol\" qiling.");
        }
        $harajatTuri->delete();
        return back()->with('muvaffaqiyat', "Harajat turi o'chirildi.");
    }

    /**
     * Eski (harajat_turi_id biriktirilmagan) harajatlarni — bir xil "turi"
     * matniga ega bo'lganlarini birgalikda — tanlangan harajat turiga va
     * kassa turiga bog'laydi, hamda har biri uchun (haqiqiy sanasi bilan)
     * Pul Oqimlariga tarixiy CHIQIM/KIRIM yozuvini yaratadi.
     */
    public function bogLash(Request $request)
    {
        $request->validate([
            'eski_turi'      => 'required|string',
            'harajat_turi_id'=> 'required|exists:harajat_turlari,id',
            'kassa_turi'     => 'required|in:naqd,terminal,bank',
        ]);

        $turi = HarajatTuri::with('kategoriya')->findOrFail($request->harajat_turi_id);
        $tulovService = app(\App\Services\TulovService::class);

        $soni = 0;
        DB::transaction(function () use ($request, $turi, $tulovService, &$soni) {
            $harajatlar = Harajat::where('turi', $request->eski_turi)
                ->whereNull('harajat_turi_id')->get();

            foreach ($harajatlar as $h) {
                $h->update([
                    'harajat_turi_id'   => $turi->id,
                    'pul_kategoriya_id' => $turi->pul_kategoriya_id,
                    'kassa_turi'        => $request->kassa_turi,
                ]);

                $summa = (float) $h->summa;
                if ($summa == 0) continue;

                $tulovService->pulOqimigaYozKassaTuri(
                    filialId: $h->filial_id,
                    kassaTuri: $request->kassa_turi,
                    summa: abs($summa),
                    sana: $h->sana->toDateString(),
                    kategoriyaKodi: $turi->kategoriya->kod,
                    izoh: $h->turi . ($h->mazmuni ? " — {$h->mazmuni}" : ''),
                    manbaTur: 'harajat',
                    manbaId: $h->id,
                    // Manfiy summa — qaytarish/kirim sifatida (masalan inkasso).
                    yunalish: $summa < 0 ? 'kirim' : 'chiqim',
                );
                $soni++;
            }
        });

        return back()->with('muvaffaqiyat', "{$soni} ta eski harajat \"{$turi->nomi}\" turiga bog'landi va Pul Oqimlariga yozildi.");
    }

    /**
     * "Таъминотчилар:" eski harajatlarini har bir TA'MINOTCHIga (mazmuni
     * matnidan taxminiy aniqlab) guruhlab ko'rsatadi — admin har guruhni
     * ko'rib chiqib tasdiqlaydi yoki to'g'rilaydi. Aniq kalit so'z (oddiy
     * matn moslashuvi) ishonchsiz chiqqani uchun (umumiy so'zlar — "тел",
     * "бозор" va h.k. ko'p taminotchida takrorlanadi) IDF-vazn usuli
     * ishlatiladi: faqat shu taminotchiga XOS so'zlar yuqori ball oladi.
     */
    public function taminotchiMigratsiya()
    {
        $taminotchilar = Taminotchi::orderBy('nomi')->get(['id', 'nomi']);

        $rows = Harajat::whereNull('harajat_turi_id')
            ->whereNull('taminotchi_tulov_id')
            ->whereNull('pul_kategoriya_id')
            ->whereRaw('LOWER(TRIM(turi)) LIKE ?', [self::TAMINOTCHI_TURI_MATN . '%'])
            ->get(['id', 'sana', 'summa', 'mazmuni']);

        [$guruhlar, $manfiylar] = $this->taminotchiGuruhla($rows, $taminotchilar);

        return view('malumotnamalar.harajat-turlari.taminotchi-migratsiya', compact('guruhlar', 'manfiylar', 'taminotchilar'));
    }

    /**
     * Bitta guruh (bitta taklif qilingan taminotchi yoki "Aniqlanmagan")
     * uchun: admin tasdiqlagan taminotchiga barcha harajatlarni
     * TaminotchiTulov sifatida ko'chiradi (kaskad kirim yopish bilan).
     * "— o'tkazib yuborish —" tanlansa hech narsa qilinmaydi.
     */
    public function taminotchiMigratsiyaTasdiq(Request $request)
    {
        $request->validate([
            'harajat_idlar'  => 'required|array|min:1',
            'harajat_idlar.*'=> 'integer|exists:harajatlar,id',
            'taminotchi_id'  => 'nullable|exists:taminotchilar,id',
            'tolov_turi'     => 'required|in:naqd,plastik,bank,offset',
        ]);

        if (!$request->taminotchi_id) {
            return back()->with('muvaffaqiyat', "O'tkazib yuborildi — bu guruh hozircha tegilmadi.");
        }

        $taminotchi = Taminotchi::findOrFail($request->taminotchi_id);
        $taminotchiController = app(TaminotchiController::class);

        $harajatlar = Harajat::whereIn('id', $request->harajat_idlar)
            ->whereNull('harajat_turi_id')->whereNull('taminotchi_tulov_id')
            ->where('summa', '>', 0)
            ->get();

        $soni = 0;
        DB::transaction(function () use ($harajatlar, $taminotchi, $taminotchiController, $request, &$soni) {
            foreach ($harajatlar as $h) {
                [$tulov] = $taminotchiController->tulovYarat($taminotchi, [
                    'summa'        => (float) $h->summa,
                    'valyuta'      => 'UZS',
                    'kurs'         => 1,
                    'tolov_sana'   => $h->sana->toDateString(),
                    'tolov_turi'   => $request->tolov_turi,
                    'kirim_id'     => null, // "Umumiy to'lov" — kaskad orqali eng eski qarzlarni yopadi
                    'hujjat_raqam' => null,
                    'izoh'         => "Eski harajatdan migratsiya: " . $h->mazmuni,
                ]);

                $h->update(['taminotchi_tulov_id' => $tulov->id]);
                $soni++;
            }
        });

        return back()->with('muvaffaqiyat', "{$soni} ta eski harajat \"{$taminotchi->nomi}\" ta'minotchisiga to'lov sifatida ko'chirildi.");
    }

    /**
     * Manfiy summali eski harajatlarni "Prochi daromad" (CF-1900 Boshqa kirimlar)
     * sifatida Pul Oqimlariga KIRIM yozib, harajat_turi_id va pul_kategoriya_id
     * o'rnatadi. Summa musbat (abs) olindi, yunalish='kirim'.
     */
    public function manfiyDaromadQilish(Request $request)
    {
        $request->validate([
            'harajat_idlar'   => 'required|array|min:1',
            'harajat_idlar.*' => 'integer|exists:harajatlar,id',
            'kassa_turi'      => 'required|in:naqd,terminal,bank',
        ]);

        $kategoriya = PulKategoriya::where('kod', 'CF-1900')->firstOrFail();
        $tulovService = app(\App\Services\TulovService::class);

        $harajatlar = Harajat::whereIn('id', $request->harajat_idlar)
            ->whereNull('harajat_turi_id')
            ->where('summa', '<', 0)
            ->get();

        $soni = 0;
        DB::transaction(function () use ($harajatlar, $kategoriya, $tulovService, $request, &$soni) {
            foreach ($harajatlar as $h) {
                $h->update([
                    'pul_kategoriya_id' => $kategoriya->id,
                    'kassa_turi'        => $request->kassa_turi,
                    'harajat_turi_id'   => null, // harajat_turi sifatida alohida tur yo'q, kategoriya to'g'ridan bog'langan
                ]);

                $tulovService->pulOqimigaYozKassaTuri(
                    filialId: $h->filial_id,
                    kassaTuri: $request->kassa_turi,
                    summa: abs((float) $h->summa),
                    sana: $h->sana->toDateString(),
                    kategoriyaKodi: 'CF-1900',
                    izoh: "Prochi daromad: " . ($h->mazmuni ?: '—'),
                    manbaTur: 'harajat',
                    manbaId: $h->id,
                    yunalish: 'kirim',
                );
                $soni++;
            }
        });

        return back()->with('muvaffaqiyat', "{$soni} ta manfiy harajat CF-1900 \"Boshqa kirimlar\" sifatida Pul Oqimlariga yozildi.");
    }

    /**
     * Harajat'larni mazmuni matni asosida eng yaqin taminotchiga guruhlaydi.
     * IDF (inverse document frequency) vaznlash — taminotchilar nomida
     * KAM uchraydigan so'zlarga (masalan shaxs ismi) ko'proq, KO'P
     * uchraydigan umumiy so'zlarga (масалан "тел", "бозор", "артел")
     * kamroq vazn beradi — bu noto'g'ri moslashuvlarni kamaytiradi.
     *
     * @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection} [guruhlar, manfiy summali (qo'lda ko'rish uchun)]
     */
    private function taminotchiGuruhla($rows, $taminotchilar): array
    {
        $sozAjrat = function (string $matn): array {
            $matn = mb_strtolower($matn, 'UTF-8');
            $matn = preg_replace('/[^a-zа-яёЁ0-9\s]/iu', ' ', $matn);
            return array_values(array_unique(array_filter(explode(' ', $matn), fn($s) => mb_strlen($s) >= 3)));
        };

        $tSozlar = [];
        foreach ($taminotchilar as $t) $tSozlar[$t->id] = $sozAjrat($t->nomi);

        $df = [];
        foreach ($tSozlar as $sozlar) {
            foreach ($sozlar as $s) $df[$s] = ($df[$s] ?? 0) + 1;
        }

        $musbat = $rows->where('summa', '>', 0);
        $manfiylar = $rows->where('summa', '<=', 0)->values();

        $guruhlar = [];
        foreach ($musbat as $h) {
            $mSozlar = $sozAjrat($h->mazmuni ?? '');
            $eng = null; $engBall = 0;
            foreach ($taminotchilar as $t) {
                $ball = 0;
                foreach (array_intersect($mSozlar, $tSozlar[$t->id]) as $w) {
                    $ball += 1 / ($df[$w] ?? 1);
                }
                if ($ball > $engBall) { $engBall = $ball; $eng = $t; }
            }

            $kalit = ($eng && $engBall >= 0.5) ? $eng->id : 0;
            $nomi  = $eng && $engBall >= 0.5 ? $eng->nomi : 'Aniqlanmagan';

            if (!isset($guruhlar[$kalit])) {
                $guruhlar[$kalit] = [
                    'taminotchi_id' => $kalit ?: null,
                    'nomi'          => $nomi,
                    'soni'          => 0,
                    'jami'          => 0,
                    'misollar'      => [],
                    'harajat_idlar' => [],
                ];
            }
            $guruhlar[$kalit]['soni']++;
            $guruhlar[$kalit]['jami'] += (float) $h->summa;
            $guruhlar[$kalit]['harajat_idlar'][] = $h->id;
            if (count($guruhlar[$kalit]['misollar']) < 4) {
                $guruhlar[$kalit]['misollar'][] = $h->mazmuni;
            }
        }

        $guruhlar = collect($guruhlar)->sortByDesc('soni')->values();
        // "Aniqlanmagan" (kalit=0) doim oxirida ko'rinsin
        $aniqlanmagan = $guruhlar->firstWhere('taminotchi_id', null);
        if ($aniqlanmagan) {
            $guruhlar = $guruhlar->reject(fn($g) => $g['taminotchi_id'] === null)->push($aniqlanmagan)->values();
        }

        return [$guruhlar, $manfiylar];
    }
}
