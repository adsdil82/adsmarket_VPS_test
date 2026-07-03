<?php
namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Taminotchi;
use App\Models\TaminotKirim;
use App\Models\TaminotKirimQator;
use App\Models\TaminotchiTulov;
use App\Models\TovarKatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaminotchiController extends Controller
{
    public function __construct(
        private \App\Services\TulovService $tulovService,
        private \App\Services\StockService $stockService,
        private \App\Services\BarcodeService $barcodeService,
    ) {}

    // ── Yordamchi ───────────────────────────────────────────────
    private function filialId(): ?int
    {
        $u = Auth::user();
        return $u->isAdmin() ? null : $u->filial_id;
    }

    // ══════════════════════════════════════════════════════════════
    // TA'MINOTCHILAR CRUD
    // ══════════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $filialId = $this->filialId();

        $danSana   = $request->dan_sana   ?? now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();
        $sort      = $request->sort ?? 'nomi';
        $dir       = $request->dir === 'desc' ? 'desc' : 'asc';

        $usdKurs = DB::table('valyutalar')->where('kod','USD')->value('kurs') ?: 1;

        // Базавий query — фильтр
        $base = Taminotchi::query()
            ->when($filialId, fn($q) => $q->filialda($filialId))
            ->when($request->holat, fn($q) => $q->where('holat', $request->holat))
            ->when($request->qidiruv, fn($q) => $q->where(fn($s) =>
                $s->where('nomi','like',"%{$request->qidiruv}%")
                  ->orWhere('telefon','like',"%{$request->qidiruv}%")
            ))
            ->select('taminotchilar.*');

        // Давр баланслари — conditional SUM ёрдамида битта queryда
        $base->selectRaw("
            COALESCE((
                SELECT SUM(jami_summa) FROM taminot_kirimlar
                WHERE taminotchi_id = taminotchilar.id
                  AND kirim_sana < ?
            ),0) AS boshi_kirim", [$danSana])
        ->selectRaw("
            COALESCE((
                SELECT SUM(summa_uzs) FROM taminotchi_tulovlar
                WHERE taminotchi_id = taminotchilar.id
                  AND tolov_sana < ?
            ),0) AS boshi_tolov", [$danSana])
        ->selectRaw("
            COALESCE((
                SELECT SUM(jami_summa) FROM taminot_kirimlar
                WHERE taminotchi_id = taminotchilar.id
                  AND kirim_sana BETWEEN ? AND ?
            ),0) AS davr_kirim", [$danSana, $gachaSana])
        ->selectRaw("
            COALESCE((
                SELECT SUM(summa_uzs) FROM taminotchi_tulovlar
                WHERE taminotchi_id = taminotchilar.id
                  AND tolov_sana BETWEEN ? AND ?
            ),0) AS davr_tolov", [$danSana, $gachaSana])
        ->selectRaw("
            COALESCE((SELECT SUM(jami_summa) FROM taminot_kirimlar WHERE taminotchi_id=taminotchilar.id AND kirim_sana < ?),0)
            - COALESCE((SELECT SUM(summa_uzs) FROM taminotchi_tulovlar WHERE taminotchi_id=taminotchilar.id AND tolov_sana < ?),0)
            AS boshi_qoldiq", [$danSana, $danSana])
        ->selectRaw("
            COALESCE((SELECT SUM(jami_summa) FROM taminot_kirimlar WHERE taminotchi_id=taminotchilar.id),0)
            - COALESCE((SELECT SUM(summa_uzs) FROM taminotchi_tulovlar WHERE taminotchi_id=taminotchilar.id),0)
            AS oxiri_qoldiq");

        $allowedSorts = ['nomi','holat','boshi_qoldiq','davr_kirim','davr_tolov','oxiri_qoldiq'];
        $base->orderBy(in_array($sort, $allowedSorts) ? $sort : 'nomi', $dir);

        $perPage = in_array((int)$request->per_page, [20,30,40,50]) ? (int)$request->per_page : 30;
        $taminotchilar = $base->paginate($perPage)->withQueryString();

        return view('taminotchi.index', compact('taminotchilar','danSana','gachaSana','usdKurs'));
    }

    public function create()
    {
        $filiallar = Auth::user()->isAdmin() ? Filial::faol()->get() : collect();
        return view('taminotchi.create', compact('filiallar'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nomi'          => 'required|string|max:200',
            'kontakt_shaxs' => 'nullable|string|max:150',
            'telefon'       => 'nullable|string|max:100',
            'telefon2'      => 'nullable|string|max:100',
            'manzil'        => 'nullable|string|max:300',
            'inn'           => 'nullable|string|max:30',
            'bank_hisob'    => 'nullable|string|max:50',
            'bank_nomi'     => 'nullable|string|max:200',
            'mfo'           => 'nullable|string|max:20',
            'asosiy_valyuta'=> 'in:UZS,USD',
            'izoh'          => 'nullable|string',
            'holat'         => 'in:faol,nofaol',
            'filial_id'     => 'nullable|exists:filiallar,id',
        ]);

        if (!Auth::user()->isAdmin()) {
            $data['filial_id'] = Auth::user()->filial_id;
        }

        $t = Taminotchi::create($data);
        return redirect()->route('taminotchi.show', $t)
            ->with('muvaffaqiyat', "Ta'minotchi «{$t->nomi}» qo'shildi.");
    }

    public function show(Taminotchi $taminotchi, Request $request)
    {
        $taminotchi->load(['filial']);

        $danSana   = $request->dan_sana   ?? now()->subMonths(3)->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();

        $kirimlar = $taminotchi->kirimlar()
            ->with(['xodim:id,ism_familiya','filial:id,kod'])
            ->whereBetween('kirim_sana', [$danSana, $gachaSana])
            ->orderByDesc('kirim_sana')
            ->get();

        $tulovlar = $taminotchi->tulovlar()
            ->with(['xodim:id,ism_familiya'])
            ->whereBetween('tolov_sana', [$danSana, $gachaSana])
            ->orderByDesc('tolov_sana')
            ->get();

        // Balans hisob
        $balans = [
            'jami_kirim' => $taminotchi->kirimlar()->sum('jami_summa'),
            'jami_tolov' => $taminotchi->tulovlar()->sum('summa_uzs'),
        ];
        $balans['qoldiq'] = $balans['jami_kirim'] - $balans['jami_tolov'];

        return view('taminotchi.show', compact(
            'taminotchi','kirimlar','tulovlar','balans','danSana','gachaSana'
        ));
    }

    public function edit(Taminotchi $taminotchi)
    {
        $filiallar = Auth::user()->isAdmin() ? Filial::faol()->get() : collect();
        return view('taminotchi.edit', compact('taminotchi','filiallar'));
    }

    public function update(Request $request, Taminotchi $taminotchi)
    {
        $data = $request->validate([
            'nomi'          => 'required|string|max:200',
            'kontakt_shaxs' => 'nullable|string|max:150',
            'telefon'       => 'nullable|string|max:100',
            'telefon2'      => 'nullable|string|max:100',
            'manzil'        => 'nullable|string|max:300',
            'inn'           => 'nullable|string|max:30',
            'bank_hisob'    => 'nullable|string|max:50',
            'bank_nomi'     => 'nullable|string|max:200',
            'mfo'           => 'nullable|string|max:20',
            'asosiy_valyuta'=> 'in:UZS,USD',
            'izoh'          => 'nullable|string',
            'holat'         => 'in:faol,nofaol',
            'filial_id'     => 'nullable|exists:filiallar,id',
        ]);
        $taminotchi->update($data);
        return redirect()->route('taminotchi.show', $taminotchi)
            ->with('muvaffaqiyat', "Ma'lumotlar yangilandi.");
    }

    // ══════════════════════════════════════════════════════════════
    // KIRIM (YETKAZIB BERISH FAKTURASI)
    // ══════════════════════════════════════════════════════════════

    public function kirimCreate(Taminotchi $taminotchi)
    {
        $tovarlar = TovarKatalog::where('holat','faol')->orderBy('nomi')->get(['id','nomi','sotish_narx','birlik','guruh_id']);
        $guruhlar = \App\Models\TovarGuruh::faol()->orderBy('nomi')->get(['id','nomi']);
        return view('taminotchi.kirim_create', compact('taminotchi','tovarlar','guruhlar'));
    }

    public function kirimStore(Request $request, Taminotchi $taminotchi)
    {
        $request->validate([
            'hujjat_raqam'   => 'nullable|string|max:50',
            'kirim_sana'     => 'required|date',
            'izoh'           => 'nullable|string',
            'qatorlar'       => 'required|array|min:1',
            'qatorlar.*.nomi'  => 'required|string',
            'qatorlar.*.miqdor'=> 'required|numeric|min:0.001',
            'qatorlar.*.narx'  => 'required|numeric|min:0',
            'qatorlar.*.guruh_id' => 'nullable|exists:tovar_guruhlar,id',
            'qatorlar.*.pos_narx'    => 'nullable|numeric|min:0',
            'qatorlar.*.nasiya_narx' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $taminotchi) {
            // Qatorlardan jami hisoblash
            $jami = 0;
            $qatorlar = collect($request->qatorlar)->map(function ($q) use (&$jami) {
                $q['jami'] = round($q['miqdor'] * $q['narx'], 2);
                $jami += $q['jami'];
                return $q;
            });

            $kirim = TaminotKirim::create([
                'taminotchi_id' => $taminotchi->id,
                'filial_id'     => $taminotchi->filial_id ?? Auth::user()->filial_id,
                'xodim_id'      => Auth::id(),
                'hujjat_raqam'  => $request->hujjat_raqam,
                'kirim_sana'    => $request->kirim_sana,
                'jami_summa'    => $jami,
                'tolangan'      => 0,
                'qoldiq'        => $jami,
                'holat'         => 'kutilmoqda',
                'izoh'          => $request->izoh,
            ]);

            foreach ($qatorlar as $q) {
                $this->kirimQatorSaqla($kirim, $q);
            }
        });

        return redirect()->route('taminotchi.show', $taminotchi)
            ->with('muvaffaqiyat', "Kirim muvaffaqiyatli qayd etildi.");
    }

    /**
     * Bitta kirim qatorini saqlaydi — agar tovar katalogda topilmasa va
     * guruh tanlangan bo'lsa, yangi tovarni katalogga ham qo'shadi
     * (POS va Nasiya narxlari bilan). kirimStore() va kirimUpdate()
     * o'rtasida umumiy.
     */
    private function kirimQatorSaqla(TaminotKirim $kirim, array $q): void
    {
        $tovarId = $q['tovar_id'] ?? null;

        if (!$tovarId && !empty($q['guruh_id'])) {
            $mavjud = TovarKatalog::where('nomi', $q['nomi'])->first();
            if ($mavjud) {
                $tovarId = $mavjud->id;
            } else {
                $yangi = TovarKatalog::create([
                    'guruh_id'    => $q['guruh_id'],
                    'nomi'        => $q['nomi'],
                    'barkod'      => $this->barcodeService->generate(),
                    'birlik'      => $q['birlik'] ?? 'dona',
                    'tan_narx'    => $q['narx'],
                    'sotish_narx' => $q['pos_narx'] ?? $q['narx'],
                    'nasiya_narx' => $q['nasiya_narx'] ?? $q['narx'],
                    'qoldiq'      => 0,
                    'min_qoldiq'  => 0,
                    'holat'       => 'faol',
                ]);
                $tovarId = $yangi->id;
            }
        }

        TaminotKirimQator::create([
            'kirim_id'  => $kirim->id,
            'tovar_id'  => $tovarId,
            'nomi'      => $q['nomi'],
            'miqdor'    => $q['miqdor'],
            'birlik'    => $q['birlik'] ?? 'dona',
            'narx'      => $q['narx'],
            'jami'      => $q['jami'],
        ]);

        // Taminotchidan tovar qabul qilinganda — omborga (filialning asosiy
        // ombori) haqiqiy miqdor kirim qilinadi. Shu bilan taminotchi kirimi
        // endi faqat moliyaviy hujjat emas, balki real ombor harakati ham.
        if ($tovarId) {
            $ombor = $this->stockService->asosiyOmbor($kirim->filial_id) ?? $this->stockService->markaziyOmbor();
            $this->stockService->kirim(
                $ombor->id, $tovarId, (float) $q['miqdor'],
                manbaTur: 'taminot_kirim', manbaId: $kirim->id,
                izoh: "Kirim" . ($kirim->hujjat_raqam ? " #{$kirim->hujjat_raqam}" : '') . " — {$q['nomi']}",
                tanNarx: (float) $q['narx'],
            );
        }
    }

    public function kirimEdit(Taminotchi $taminotchi, TaminotKirim $kirim)
    {
        if ($kirim->taminotchi_id !== $taminotchi->id) abort(404);

        $kirim->load('qatorlar');
        $tovarlar = TovarKatalog::where('holat','faol')->orderBy('nomi')->get(['id','nomi','sotish_narx','birlik','guruh_id']);
        $guruhlar = \App\Models\TovarGuruh::faol()->orderBy('nomi')->get(['id','nomi']);

        return view('taminotchi.kirim_edit', compact('taminotchi','kirim','tovarlar','guruhlar'));
    }

    public function kirimUpdate(Request $request, Taminotchi $taminotchi, TaminotKirim $kirim)
    {
        if ($kirim->taminotchi_id !== $taminotchi->id) abort(404);

        $request->validate([
            'hujjat_raqam'   => 'nullable|string|max:50',
            'kirim_sana'     => 'required|date',
            'izoh'           => 'nullable|string',
            'qatorlar'       => 'required|array|min:1',
            'qatorlar.*.nomi'  => 'required|string',
            'qatorlar.*.miqdor'=> 'required|numeric|min:0.001',
            'qatorlar.*.narx'  => 'required|numeric|min:0',
            'qatorlar.*.guruh_id' => 'nullable|exists:tovar_guruhlar,id',
            'qatorlar.*.pos_narx'    => 'nullable|numeric|min:0',
            'qatorlar.*.nasiya_narx' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $kirim) {
            $jami = 0;
            $qatorlar = collect($request->qatorlar)->map(function ($q) use (&$jami) {
                $q['jami'] = round($q['miqdor'] * $q['narx'], 2);
                $jami += $q['jami'];
                return $q;
            });

            // Eski qatorlarning ombor ta'sirini bekor qilamiz (chiqim sifatida
            // qaytarib olamiz), keyin qatorlarni o'chirib, yangilarini qayta
            // yozamiz — bu orqali miqdor o'zgarishi ombor qoldig'ida ham
            // to'g'ri aks etadi.
            $ombor = $this->stockService->asosiyOmbor($kirim->filial_id) ?? $this->stockService->markaziyOmbor();
            foreach ($kirim->qatorlar as $eski) {
                if (!$eski->tovar_id) continue;
                if ($this->stockService->yetarlimi($ombor->id, $eski->tovar_id, (float) $eski->miqdor)) {
                    $this->stockService->chiqim(
                        $ombor->id, $eski->tovar_id, (float) $eski->miqdor,
                        manbaTur: 'taminot_kirim_tahrir', manbaId: $kirim->id,
                        izoh: "Kirim tahrirlash — eski qator bekor qilindi: {$eski->nomi}",
                        harakat: 'tuzatish',
                    );
                }
                // Yetarli bo'lmasa (masalan qisman sotilgan) — qoldiqni manfiyga
                // olib bormaymiz, shu holatda faqat ombordagi mavjudini nolga
                // tushiramiz va qolganini tarixiy farq sifatida qoldiramiz.
            }

            $kirim->qatorlar()->delete();
            foreach ($qatorlar as $q) {
                $this->kirimQatorSaqla($kirim, $q);
            }

            // To'langan summa o'zgarmaydi, faqat jami/qoldiq/holat qayta hisoblanadi.
            $yangiQoldiq = max(0, $jami - $kirim->tolangan);
            $kirim->update([
                'hujjat_raqam' => $request->hujjat_raqam,
                'kirim_sana'   => $request->kirim_sana,
                'izoh'         => $request->izoh,
                'jami_summa'   => $jami,
                'qoldiq'       => $yangiQoldiq,
                'holat'        => $yangiQoldiq == 0 ? 'toliq' : ($kirim->tolangan > 0 ? 'qisman' : 'kutilmoqda'),
            ]);
        });

        return redirect()->route('taminotchi.show', $taminotchi)
            ->with('muvaffaqiyat', 'Kirim yangilandi.');
    }

    // ══════════════════════════════════════════════════════════════
    // TO'LOV KIRITISH
    // ══════════════════════════════════════════════════════════════

    public function tulovStore(Request $request, Taminotchi $taminotchi)
    {
        $request->validate([
            'summa'       => 'required|numeric|min:0.01',
            'tolov_sana'  => 'required|date',
            'tolov_turi'  => 'in:naqd,plastik,bank,offset,hisobdan_chiqarish,ustav',
            'valyuta'     => 'in:UZS,USD',
            'kurs'        => 'nullable|numeric|min:1',
            'kirim_id'    => 'nullable|exists:taminot_kirimlar,id',
            'hujjat_raqam'=> 'nullable|string|max:50',
            'izoh'        => 'nullable|string',
        ]);

        [$tulov, $taqsimot] = $this->tulovYarat($taminotchi, [
            'summa'        => (float) $request->summa,
            'valyuta'      => $request->valyuta ?: 'UZS',
            'kurs'         => (float) ($request->kurs ?: 1),
            'tolov_sana'   => $request->tolov_sana,
            'tolov_turi'   => $request->tolov_turi ?: 'naqd',
            'kirim_id'     => $request->kirim_id ?: null,
            'hujjat_raqam' => $request->hujjat_raqam,
            'izoh'         => $request->izoh,
        ]);

        $valyuta  = $tulov->valyuta;
        $summa    = (float) $tulov->summa;
        $summaUzs = (float) $tulov->summa_uzs;

        $xabarSumma = $valyuta === 'UZS'
            ? number_format($summa, 0, '.', ' ') . " so'm"
            : number_format($summa, 0, '.', ' ') . " {$valyuta} (" . number_format($summaUzs, 0, '.', ' ') . " so'm)";

        $xabar = "{$xabarSumma} to'lov kiritildi.";
        if (count($taqsimot) > 1) {
            $xabar .= " Summa " . count($taqsimot) . " ta kirimga taqsimlandi (ortig'i keyingi qarzlarni yopdi).";
        }

        return back()->with('muvaffaqiyat', $xabar);
    }

    /**
     * To'lov yaratishning umumiy yadrosi — tulovStore() va eski harajatlarni
     * taminotchiga migratsiya qilish vositasi (HarajatTuriController::
     * taminotchiMigratsiyaTasdiq()) tomonidan bir xil mantiq bilan
     * ishlatiladi: kaskad kirim taqsimoti, TaminotchiTulov yozuvi, Pul
     * Oqimlariga avtomatik CHIQIM yozish.
     *
     * @param array $malumot ['summa','valyuta','kurs','tolov_sana','tolov_turi','kirim_id','hujjat_raqam','izoh']
     * @return array{0: TaminotchiTulov, 1: array} [yaratilgan to'lov, kirim_taqsimot]
     */
    public function tulovYarat(Taminotchi $taminotchi, array $malumot): array
    {
        $valyuta  = $malumot['valyuta'] ?: 'UZS';
        $kurs     = (float) ($malumot['kurs'] ?: 1);
        $summa    = (float) $malumot['summa'];
        $summaUzs = $valyuta === 'UZS' ? $summa : round($summa * $kurs, 2);
        $kirimId  = $malumot['kirim_id'] ?? null;

        $tulov = null;
        $taqsimot = [];

        DB::transaction(function () use ($taminotchi, $malumot, $valyuta, $kurs, $summa, $summaUzs, $kirimId, &$tulov, &$taqsimot) {
            $filialId = $taminotchi->filial_id ?? Auth::user()->filial_id;

            // Kirim tanlangan bo'lsa va to'lov summasi shu kirimning qoldig'idan
            // ko'p bo'lsa — ortig'ini keyingi (sana bo'yicha) to'lanmagan
            // kirimlarga "kaskad" qilib yopamiz.
            if ($kirimId) {
                $qoldiqSumma = $summaUzs;

                $birinchiKirim = TaminotKirim::find($kirimId);
                if ($birinchiKirim) {
                    $yopildi = min($qoldiqSumma, $birinchiKirim->qoldiq);
                    if ($yopildi > 0) {
                        $taqsimot[] = ['kirim_id' => $birinchiKirim->id, 'summa' => $yopildi];
                        $qoldiqSumma -= $yopildi;
                    }

                    if ($qoldiqSumma > 0) {
                        $keyingiKirimlar = TaminotKirim::where('taminotchi_id', $taminotchi->id)
                            ->where('id', '!=', $birinchiKirim->id)
                            ->where('qoldiq', '>', 0)
                            ->orderBy('kirim_sana')->orderBy('id')
                            ->get();

                        foreach ($keyingiKirimlar as $k) {
                            if ($qoldiqSumma <= 0) break;
                            $yopildi = min($qoldiqSumma, $k->qoldiq);
                            if ($yopildi > 0) {
                                $taqsimot[] = ['kirim_id' => $k->id, 'summa' => $yopildi];
                                $qoldiqSumma -= $yopildi;
                            }
                        }
                    }
                }
            }

            $tulov = TaminotchiTulov::create([
                'taminotchi_id' => $taminotchi->id,
                'kirim_id'      => $kirimId,
                'kirim_taqsimot'=> $taqsimot ?: null,
                'xodim_id'      => Auth::id(),
                'filial_id'     => $filialId,
                'summa'         => $summa,
                'valyuta'       => $valyuta,
                'kurs'          => $kurs,
                'summa_uzs'     => $summaUzs,
                'tolov_sana'    => $malumot['tolov_sana'],
                'tolov_turi'    => $malumot['tolov_turi'] ?: 'naqd',
                'hujjat_raqam'  => $malumot['hujjat_raqam'] ?? null,
                'izoh'          => $malumot['izoh'] ?? null,
            ]);

            // To'lov turiga qarab Pul Oqimlariga mos yozuv yaratamiz:
            //   naqd/plastik/bank  → CF-2300 CHIQIM (real pul harakati)
            //   hisobdan_chiqarish → CF-1900 KIRIM  (kechirilingan qarz = daromad)
            //   ustav              → CF-1500 KIRIM  (kapital hissasi)
            //   offset             → hech narsa     (pulsiz hisob-kitob)
            $izoh = "Ta'minotchi \"{$taminotchi->nomi}\"" . (!empty($malumot['hujjat_raqam']) ? " ({$malumot['hujjat_raqam']})" : '');

            $kassaTuriMap = ['naqd' => 'naqd', 'plastik' => 'terminal', 'bank' => 'bank'];

            if (isset($kassaTuriMap[$tulov->tolov_turi]) && $filialId) {
                $this->tulovService->pulOqimigaYozKassaTuri(
                    filialId: $filialId,
                    kassaTuri: $kassaTuriMap[$tulov->tolov_turi],
                    summa: $summaUzs,
                    sana: $malumot['tolov_sana'],
                    kategoriyaKodi: 'CF-2300',
                    izoh: $izoh . " — to'lov",
                    manbaTur: 'taminotchi_tulov',
                    manbaId: $tulov->id,
                    yunalish: 'chiqim',
                );
            } elseif ($tulov->tolov_turi === 'hisobdan_chiqarish' && $filialId) {
                $this->tulovService->pulOqimigaYozKassaTuri(
                    filialId: $filialId,
                    kassaTuri: 'naqd', // virtual yozuv, lekin kassa tanlash kerak
                    summa: $summaUzs,
                    sana: $malumot['tolov_sana'],
                    kategoriyaKodi: 'CF-1900',
                    izoh: $izoh . " — qarz hisobdan chiqarildi (daromadga olindi)",
                    manbaTur: 'taminotchi_tulov',
                    manbaId: $tulov->id,
                    yunalish: 'kirim',
                );
            } elseif ($tulov->tolov_turi === 'ustav' && $filialId) {
                $this->tulovService->pulOqimigaYozKassaTuri(
                    filialId: $filialId,
                    kassaTuri: 'naqd',
                    summa: $summaUzs,
                    sana: $malumot['tolov_sana'],
                    kategoriyaKodi: 'CF-1500',
                    izoh: $izoh . " — ustav kapitaliga hissa",
                    manbaTur: 'taminotchi_tulov',
                    manbaId: $tulov->id,
                    yunalish: 'kirim',
                );
            }
            // offset: pulsiz hisob-kitob — Pul Oqimlariga yozilmaydi.

            // Kaskad taqsimot bo'yicha har bir kirimning to'langan/qoldig'ini
            // yangilaymiz. Kirim (tovar qabul qilish) HAR DOIM so'mda, shuning
            // uchun bu yerda ham summaUzs (so'm ekvivalenti) ishlatiladi.
            foreach ($taqsimot as $t) {
                $kirim = TaminotKirim::find($t['kirim_id']);
                if (!$kirim) continue;
                $yangiTolangan = $kirim->tolangan + $t['summa'];
                $yangiQoldiq   = max(0, $kirim->jami_summa - $yangiTolangan);
                $kirim->update([
                    'tolangan' => $yangiTolangan,
                    'qoldiq'   => $yangiQoldiq,
                    'holat'    => $yangiQoldiq == 0 ? 'toliq' : 'qisman',
                ]);
            }
        });

        return [$tulov, $taqsimot];
    }

    /**
     * Ta'minotchiga qilingan to'lovni o'chirish — qarzdorlikni va bog'langan
     * kirim qoldig'ini tiklaydi, hamda shu to'lovdan avtomatik yaratilgan
     * "Pul oqimlari" yozuvini ham o'chiradi (faqat shu yerdan o'chirish
     * to'g'ri — Pul Oqimlari modulidan emas, chunki u yerda faqat qarzdorlik
     * emas, pul harakati ko'rsatiladi).
     */
    public function tulovDestroy(Taminotchi $taminotchi, TaminotchiTulov $tulov)
    {
        if ($tulov->taminotchi_id !== $taminotchi->id) {
            abort(404);
        }

        DB::transaction(function () use ($taminotchi, $tulov) {
            // Bog'langan kirim(lar)ning to'langan/qoldiq holatini tiklash.
            // Kaskad taqsimot bo'lsa — har bir kirimga tegishli aniq summani
            // qaytaramiz; eski (taqsimotsiz) yozuvlar uchun butun summa
            // bitta kirim_id'ga qaytariladi.
            $taqsimot = $tulov->kirim_taqsimot;
            if (!$taqsimot && $tulov->kirim_id) {
                $taqsimot = [['kirim_id' => $tulov->kirim_id, 'summa' => $tulov->summa_uzs]];
            }

            foreach ((array)$taqsimot as $t) {
                $kirim = TaminotKirim::find($t['kirim_id']);
                if (!$kirim) continue;
                $yangiTolangan = max(0, $kirim->tolangan - $t['summa']);
                $yangiQoldiq   = max(0, $kirim->jami_summa - $yangiTolangan);
                $kirim->update([
                    'tolangan' => $yangiTolangan,
                    'qoldiq'   => $yangiQoldiq,
                    'holat'    => $yangiQoldiq == 0 ? 'toliq' : ($yangiTolangan > 0 ? 'qisman' : 'kutilmoqda'),
                ]);
            }

            $this->tulovService->pulOqiminiOchir('taminotchi_tulov', $tulov->id);
            $tulov->delete();
        });

        return back()->with('muvaffaqiyat', "To'lov o'chirildi, qarzdorlik tiklandi.");
    }

    /**
     * To'lovni tahrirlash — eski kirim ta'sirini bekor qilib, qayta hisoblaydi.
     * Mantiq: 1) eski kirim ta'sirini qaytarish + PulOqim o'chirish,
     *         2) to'lov yozuvini yangi qiymatlar bilan yangilash,
     *         3) yangi kirim kaskadini qayta qo'llash + PulOqim yaratish.
     */
    public function tulovUpdate(Request $request, Taminotchi $taminotchi, TaminotchiTulov $tulov)
    {
        if ($tulov->taminotchi_id !== $taminotchi->id) abort(404);

        $request->validate([
            'summa'       => 'required|numeric|min:0.01',
            'tolov_sana'  => 'required|date',
            'tolov_turi'  => 'required|in:naqd,plastik,bank,offset,hisobdan_chiqarish,ustav',
            'valyuta'     => 'in:UZS,USD',
            'kurs'        => 'nullable|numeric|min:1',
            'hujjat_raqam'=> 'nullable|string|max:50',
            'izoh'        => 'nullable|string',
        ]);

        $valyuta  = $request->valyuta ?: 'UZS';
        $kurs     = (float)($request->kurs ?: 1);
        $summa    = (float)$request->summa;
        $summaUzs = $valyuta === 'UZS' ? $summa : round($summa * $kurs, 2);

        DB::transaction(function () use ($request, $taminotchi, $tulov, $valyuta, $kurs, $summa, $summaUzs) {
            // 1) Eski kirim ta'sirini qaytarish
            $eskiTaqsimot = $tulov->kirim_taqsimot;
            if (!$eskiTaqsimot && $tulov->kirim_id) {
                $eskiTaqsimot = [['kirim_id' => $tulov->kirim_id, 'summa' => $tulov->summa_uzs]];
            }
            foreach ((array)$eskiTaqsimot as $t) {
                $kirim = TaminotKirim::find($t['kirim_id']);
                if (!$kirim) continue;
                $yangiTolangan = max(0, $kirim->tolangan - $t['summa']);
                $yangiQoldiq   = max(0, $kirim->jami_summa - $yangiTolangan);
                $kirim->update([
                    'tolangan' => $yangiTolangan, 'qoldiq' => $yangiQoldiq,
                    'holat' => $yangiQoldiq == 0 ? 'toliq' : ($yangiTolangan > 0 ? 'qisman' : 'kutilmoqda'),
                ]);
            }

            // 2) Eski PulOqim o'chirish
            $this->tulovService->pulOqiminiOchir('taminotchi_tulov', $tulov->id);

            // 3) Yangi kirim kaskad taqsimotini hisoblash (eski kirim_id saqlanadi)
            $yangiTaqsimot = [];
            if ($tulov->kirim_id) {
                $qoldiqSumma    = $summaUzs;
                $birinchiKirim  = TaminotKirim::find($tulov->kirim_id);
                if ($birinchiKirim) {
                    $yopildi = min($qoldiqSumma, $birinchiKirim->qoldiq);
                    if ($yopildi > 0) { $yangiTaqsimot[] = ['kirim_id' => $birinchiKirim->id, 'summa' => $yopildi]; $qoldiqSumma -= $yopildi; }
                    if ($qoldiqSumma > 0) {
                        foreach (TaminotKirim::where('taminotchi_id', $taminotchi->id)->where('id','!=',$birinchiKirim->id)->where('qoldiq','>',0)->orderBy('kirim_sana')->get() as $k) {
                            if ($qoldiqSumma <= 0) break;
                            $yopildi = min($qoldiqSumma, $k->qoldiq);
                            if ($yopildi > 0) { $yangiTaqsimot[] = ['kirim_id' => $k->id, 'summa' => $yopildi]; $qoldiqSumma -= $yopildi; }
                        }
                    }
                }
            }

            // 4) To'lov yozuvini yangilash
            $tulov->update([
                'summa'          => $summa,
                'valyuta'        => $valyuta,
                'kurs'           => $kurs,
                'summa_uzs'      => $summaUzs,
                'tolov_sana'     => $request->tolov_sana,
                'tolov_turi'     => $request->tolov_turi,
                'hujjat_raqam'   => $request->hujjat_raqam,
                'izoh'           => $request->izoh,
                'kirim_taqsimot' => $yangiTaqsimot ?: null,
            ]);

            // 5) Yangi kirim ta'sirini qo'llash
            foreach ($yangiTaqsimot as $t) {
                $kirim = TaminotKirim::find($t['kirim_id']);
                if (!$kirim) continue;
                $yangiTolangan = $kirim->tolangan + $t['summa'];
                $yangiQoldiq   = max(0, $kirim->jami_summa - $yangiTolangan);
                $kirim->update(['tolangan'=>$yangiTolangan,'qoldiq'=>$yangiQoldiq,'holat'=>$yangiQoldiq==0?'toliq':'qisman']);
            }

            // 6) Yangi PulOqim yozuvi
            $filialId = $taminotchi->filial_id ?? Auth::user()->filial_id;
            $izoh = "Ta'minotchi \"{$taminotchi->nomi}\"" . ($request->hujjat_raqam ? " ({$request->hujjat_raqam})" : '');
            $kassaTuriMap = ['naqd'=>'naqd','plastik'=>'terminal','bank'=>'bank'];
            if (isset($kassaTuriMap[$request->tolov_turi]) && $filialId) {
                $this->tulovService->pulOqimigaYozKassaTuri($filialId,$kassaTuriMap[$request->tolov_turi],$summaUzs,$request->tolov_sana,'CF-2300',$izoh." — to'lov",'taminotchi_tulov',$tulov->id,'chiqim');
            } elseif ($request->tolov_turi === 'hisobdan_chiqarish' && $filialId) {
                $this->tulovService->pulOqimigaYozKassaTuri($filialId,'naqd',$summaUzs,$request->tolov_sana,'CF-1900',$izoh." — qarz hisobdan chiqarildi",'taminotchi_tulov',$tulov->id,'kirim');
            } elseif ($request->tolov_turi === 'ustav' && $filialId) {
                $this->tulovService->pulOqimigaYozKassaTuri($filialId,'naqd',$summaUzs,$request->tolov_sana,'CF-1500',$izoh." — ustav kapitaliga hissa",'taminotchi_tulov',$tulov->id,'kirim');
            }
        });

        return back()->with('muvaffaqiyat', "To'lov yangilandi.");
    }

    // ══════════════════════════════════════════════════════════════
    // AKT SVERKA
    // ══════════════════════════════════════════════════════════════

    public function aktSverka(Taminotchi $taminotchi, Request $request)
    {
        $danSana   = $request->dan_sana   ?? now()->startOfYear()->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();

        // Boshlanish qoldig'i (dan_sana gacha) — har doim so'mda (summa_uzs)
        $boshlKirim = $taminotchi->kirimlar()->where('kirim_sana','<',$danSana)->sum('jami_summa');
        $boshlTolov = $taminotchi->tulovlar()->where('tolov_sana','<',$danSana)->sum('summa_uzs');
        $boshlQoldiq = $boshlKirim - $boshlTolov;

        // Davr harakatlari
        $kirimlar = $taminotchi->kirimlar()
            ->whereBetween('kirim_sana', [$danSana, $gachaSana])
            ->orderBy('kirim_sana')->get();

        $tulovlar = $taminotchi->tulovlar()
            ->whereBetween('tolov_sana', [$danSana, $gachaSana])
            ->with('xodim:id,ism_familiya')
            ->orderBy('tolov_sana')->get();

        // Umumlashtirilgan harakat ro'yxati (xronologik)
        $harakatlar = collect();
        foreach ($kirimlar as $k) {
            $harakatlar->push([
                'sana'    => $k->kirim_sana,
                'tur'     => 'kirim',
                'tavsif'  => "Kirim ({$k->hujjat_raqam})",
                'debet'   => $k->jami_summa,   // biz qarz oldik
                'kredit'  => 0,
                'model'   => $k,
            ]);
        }
        foreach ($tulovlar as $t) {
            // Chet valyutada to'langan bo'lsa — tavsifda valyuta miqdori va
            // kursini ham ko'rsatamiz (summalar har doim so'mda hisoblanadi).
            $valyutaQismi = $t->valyuta !== 'UZS'
                ? sprintf(' — %s %s × %s so\'m', number_format($t->summa,0,'.',' '), $t->valyuta, number_format($t->kurs,0,'.',' '))
                : '';
            $harakatlar->push([
                'sana'    => $t->tolov_sana,
                'tur'     => 'tolov',
                'tavsif'  => "To'lov ({$t->tolov_turi}){$valyutaQismi}" . ($t->izoh ? " — {$t->izoh}" : ''),
                'debet'   => 0,
                'kredit'  => $t->summa_uzs,     // biz to'ladik (so'mda)
                'model'   => $t,
            ]);
        }
        $harakatlar = $harakatlar->sortBy('sana')->values();

        // Qoldiqni qayta hisoblash
        $joriy = $boshlQoldiq;
        $harakatlar = $harakatlar->map(function ($h) use (&$joriy) {
            $joriy += $h['debet'] - $h['kredit'];
            $h['qoldiq'] = $joriy;
            return $h;
        });

        $yakunQoldiq = $boshlQoldiq + $kirimlar->sum('jami_summa') - $tulovlar->sum('summa_uzs');

        return view('taminotchi.akt_sverka', compact(
            'taminotchi','harakatlar','boshlQoldiq','yakunQoldiq',
            'danSana','gachaSana','kirimlar','tulovlar'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    // HISOBOT — barcha ta'minotchilar bo'yicha
    // ══════════════════════════════════════════════════════════════

    public function hisobot(Request $request)
    {
        $filialId  = $this->filialId();
        $filiallar = Auth::user()->isAdmin() ? Filial::faol()->get() : collect();
        $danSana   = $request->dan_sana   ?? now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();

        $statistika = DB::table('taminotchilar as t')
            ->when($filialId, fn($q) => $q->where(fn($s) =>
                $s->where('t.filial_id',$filialId)->orWhereNull('t.filial_id')
            ))
            ->when($request->filial_id, fn($q) => $q->where('t.filial_id',$request->filial_id))
            ->selectRaw("
                t.id, t.nomi, t.telefon, t.holat,
                COALESCE(SUM(DISTINCT k.jami_summa),0) as jami_kirim,
                COALESCE(SUM(DISTINCT tv.summa_uzs),0)    as jami_tolov,
                COALESCE(SUM(DISTINCT k.jami_summa),0) - COALESCE(SUM(DISTINCT tv.summa_uzs),0) as qoldiq,
                COUNT(DISTINCT k.id) as kirim_soni,
                COUNT(DISTINCT tv.id) as tulov_soni
            ")
            ->leftJoin('taminot_kirimlar as k', fn($j) =>
                $j->on('k.taminotchi_id','=','t.id')
                  ->whereBetween('k.kirim_sana', [$danSana, $gachaSana])
            )
            ->leftJoin('taminotchi_tulovlar as tv', fn($j) =>
                $j->on('tv.taminotchi_id','=','t.id')
                  ->whereBetween('tv.tolov_sana', [$danSana, $gachaSana])
            )
            ->groupBy('t.id','t.nomi','t.telefon','t.holat')
            ->orderByDesc('qoldiq')
            ->get();

        $jami = [
            'kirim'  => $statistika->sum('jami_kirim'),
            'tolov'  => $statistika->sum('jami_tolov'),
            'qoldiq' => $statistika->sum('qoldiq'),
            'qarazdor' => $statistika->where('qoldiq','>',0)->count(), // biz qarazdormiz
            'hakdor'   => $statistika->where('qoldiq','<',0)->count(), // ular bizga qarazdor
        ];

        if ($request->format === 'excel') {
            return $this->excelHisobot($statistika, $danSana, $gachaSana);
        }

        return view('taminotchi.hisobot', compact(
            'statistika','jami','filiallar','filialId','danSana','gachaSana'
        ));
    }

    private function excelHisobot($statistika, $dan, $gacha)
    {
        $html  = '<html xmlns:o="urn:schemas-microsoft-com:office:office">';
        $html .= '<head><meta charset="UTF-8"></head><body>';
        $html .= "<h3>Ta'minotchilar hisoboti: $dan — $gacha</h3>";
        $html .= '<table border="1"><thead><tr>';
        foreach (['#','Nomi','Telefon','Kirim','To\'lov','Qoldiq','Holat'] as $h)
            $html .= "<th>$h</th>";
        $html .= '</tr></thead><tbody>';
        foreach ($statistika as $i => $r) {
            $html .= "<tr><td>" . ($i+1) . "</td><td>{$r->nomi}</td><td>{$r->telefon}</td>";
            $html .= "<td style='text-align:right'>" . number_format($r->jami_kirim,0,'.',' ') . "</td>";
            $html .= "<td style='text-align:right'>" . number_format($r->jami_tolov,0,'.',' ') . "</td>";
            $html .= "<td style='text-align:right'>" . number_format($r->qoldiq,0,'.',' ') . "</td>";
            $html .= "<td>" . ($r->qoldiq > 0 ? 'Qarazdor' : ($r->qoldiq < 0 ? 'Hakdor' : 'Teng')) . "</td>";
            $html .= "</tr>";
        }
        $html .= '</tbody></table></body></html>';
        $fn = 'taminotchi_hisobot_' . now()->format('Ymd') . '.xls';
        return response($html,200,['Content-Type'=>'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition'=>"attachment; filename=\"$fn\""]);
    }

    // ── To'lovlar reestri ────────────────────────────────────────
    public function tulovReestr(Request $request)
    {
        $filialId  = $this->filialId();
        $filiallar = Auth::user()->isAdmin() ? Filial::faol()->get() : collect();
        $danSana   = $request->dan_sana   ?? now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();

        $tulovlar = TaminotchiTulov::with(['taminotchi','xodim:id,ism_familiya','filial:id,kod'])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->when($request->taminotchi_id, fn($q) => $q->where('taminotchi_id', $request->taminotchi_id))
            ->whereBetween('tolov_sana', [$danSana, $gachaSana])
            ->orderByDesc('tolov_sana')
            ->paginate(50)->withQueryString();

        $taminotchilar = Taminotchi::faol()
            ->when($filialId, fn($q) => $q->filialda($filialId))
            ->orderBy('nomi')->get();

        return view('taminotchi.tulov_reestr', compact(
            'tulovlar','taminotchilar','filiallar','filialId','danSana','gachaSana'
        ));
    }

    /**
     * Kirimlar reestri — chap panelda ta'minotchilar ro'yxati (qidiruv bilan),
     * o'ng panelda tanlangan ta'minotchining barcha kirimlari: sana, tovar
     * soni, jami summa (so'm) va shu ta'minotchi asosiy valyutasidagi
     * ekvivalenti (jorij kurs bo'yicha).
     */
    public function kirimReestr(Request $request)
    {
        $filialId  = $this->filialId();
        $usdKurs   = DB::table('valyutalar')->where('kod','USD')->value('kurs') ?: 1;

        $taminotchilar = Taminotchi::faol()
            ->when($filialId, fn($q) => $q->filialda($filialId))
            ->when($request->qidiruv, fn($q) => $q->where('nomi','like',"%{$request->qidiruv}%"))
            ->withCount('kirimlar')
            ->orderBy('nomi')
            ->get();

        $tanlangan = null;
        $kirimlar  = collect();

        if ($request->taminotchi_id) {
            $tanlangan = Taminotchi::find($request->taminotchi_id);
            if ($tanlangan) {
                $kirimlar = TaminotKirim::where('taminotchi_id', $tanlangan->id)
                    ->withCount('qatorlar')
                    ->orderByDesc('kirim_sana')->orderByDesc('id')
                    ->get();
            }
        }

        return view('taminotchi.kirim_reestr', compact(
            'taminotchilar','tanlangan','kirimlar','usdKurs'
        ));
    }
}
