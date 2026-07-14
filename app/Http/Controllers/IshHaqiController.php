<?php

namespace App\Http\Controllers;

use App\Models\BonusTuri;
use App\Models\DamOlishKuni;
use App\Models\DavomatOyHolati;
use App\Models\Filial;
use App\Models\Foydalanuvchi;
use App\Models\IshHaqiAvans;
use App\Models\IshHaqiGlobalSozlama;
use App\Models\IshHaqiHisob;
use App\Models\MehnatShartnomaShabloni;
use App\Models\XodimBonus;
use App\Models\XodimDavomat;
use App\Models\XodimIshHaqiSozlama;
use App\Models\XodimShartnoma;
use App\Models\XodimTatil;
use App\Services\IshHaqiHisoblashService;
use App\Services\XodimBoshqaruvService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Xodimlar ish haqi moduli — bitta sahifada tab uslubida:
 * Xodimlar / Davomat (tabel) / Hisoblash / Tarix / Sozlamalar / Dashboard.
 */
class IshHaqiController extends Controller
{
    public function __construct(
        private IshHaqiHisoblashService $svc,
        private XodimBoshqaruvService $xodimSvc,
    ) {}

    private const TABLAR = ['xodimlar', 'davomat', 'hisoblash', 'tarix', 'sozlamalar', 'dashboard'];

    public function index(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;
        $tab      = in_array($request->get('tab'), self::TABLAR, true) ? $request->get('tab') : 'davomat';
        $qidiruv  = trim((string) $request->get('qidiruv'));

        $yil         = (int) $request->get('yil', now()->format('Y'));
        $oy          = (int) $request->get('oy', now()->format('n'));
        $holat       = $request->get('holat');
        $manba       = in_array($request->get('manba'), ['tizim', 'qolda'], true) ? $request->get('manba') : null;

        $xodimlar     = collect();
        $davomatlar   = [];
        $hisoblar     = collect();
        $oldingiQoldiqlar = collect();
        $sozlamaXodimlar = collect();
        $tarixHisoblar = collect();
        $statistika   = [];
        $reyting      = collect();
        $kunlarSoni   = 30;
        $oyBoshi      = null;
        $oyYopiqmi    = false;
        $globalSozlama = IshHaqiGlobalSozlama::ol();
        $bonusTurlari  = collect();
        $shartnomaShablonlari = collect();
        $mavjudFoydalanuvchilar = collect();
        $kalendarYil   = (int) $request->get('kalendar_yil', now()->year);
        $damOlishKalendar = collect();

        if ($tab === 'xodimlar') {
            $xodimlar = Foydalanuvchi::with([
                    'ishHaqiSozlama', 'filial',
                    'tatillar' => fn($q) => $q->latest('boshlanish_sana')->limit(1),
                    'bonuslar' => fn($q) => $q->where('holat', 'faol')->with('bonusTuri')->latest(),
                    'shartnomalar' => fn($q) => $q->latest(),
                ])
                ->whereHas('ishHaqiSozlama')
                ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                ->when($qidiruv, fn($q) => $q->where('ism_familiya', 'like', "%{$qidiruv}%"))
                ->orderBy('ism_familiya')->get();

            $bonusTurlari = BonusTuri::orderBy('sort_order')->get();
            $shartnomaShablonlari = MehnatShartnomaShabloni::orderBy('sort_order')->get();

            // "Xodim qo'shish" modalida tanlash uchun — hali xodim profili ochilmagan tizim foydalanuvchilari.
            $mavjudFoydalanuvchilar = Foydalanuvchi::where('tizimga_kirish_bormi', true)
                ->whereDoesntHave('ishHaqiSozlama')
                ->orderBy('ism_familiya')->get();
        } elseif ($tab === 'davomat') {
            // Faqat "Xodimlar" ro'yxatidagi faol (ishdan bo'shatilmagan) xodimlar tabelga kiradi.
            $xodimlar = Foydalanuvchi::faol()
                ->whereHas('ishHaqiSozlama', fn($q) => $q->where('holat', 'faol'))
                ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                ->when($manba === 'tizim', fn($q) => $q->where('tizimga_kirish_bormi', true))
                ->when($manba === 'qolda', fn($q) => $q->where('tizimga_kirish_bormi', false))
                ->when($qidiruv, fn($q) => $q->where('ism_familiya', 'like', "%{$qidiruv}%"))
                ->orderBy('ism_familiya')->get();

            $oyBoshi    = Carbon::create($yil, $oy, 1)->startOfMonth();
            $kunlarSoni = $oyBoshi->daysInMonth;
            $oyYopiqmi  = DavomatOyHolati::yopiqmi($yil, $oy);
            $damOlishKalendar = DamOlishKuni::shuYilgi($yil);

            $davomatRaw = XodimDavomat::whereYear('sana', $yil)->whereMonth('sana', $oy)
                ->whereIn('xodim_id', $xodimlar->pluck('id'))
                ->get();

            foreach ($davomatRaw as $d) {
                $davomatlar[$d->xodim_id][(int) $d->sana->format('j')] = $d->holat;
            }
        } elseif ($tab === 'hisoblash') {
            $xodimlar = Foydalanuvchi::faol()
                ->whereHas('ishHaqiSozlama')
                ->with('ishHaqiSozlama')
                ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                ->when($qidiruv, fn($q) => $q->where('ism_familiya', 'like', "%{$qidiruv}%"))
                ->orderBy('ism_familiya')->get();

            $hisoblar = IshHaqiHisob::where('yil', $yil)->where('oy', $oy)
                ->whereIn('xodim_id', $xodimlar->pluck('id'))
                ->get()->keyBy('xodim_id');

            // Oldingi (to'lanmagan) oylardan qolgan qoldiq — xodimga hali to'lanmagan
            // avvalgi hisoblarning (jami - avans) yig'indisi + tizimga qo'shilishidan
            // oldingi bir martalik "dastlabki qoldiq".
            $eskiHisoblarQoldigi = IshHaqiHisob::whereIn('xodim_id', $xodimlar->pluck('id'))
                ->where('holat', 'hisoblangan')
                ->where(function ($q) use ($yil, $oy) {
                    $q->where('yil', '<', $yil)
                      ->orWhere(function ($qq) use ($yil, $oy) {
                          $qq->where('yil', $yil)->where('oy', '<', $oy);
                      });
                })
                ->selectRaw('xodim_id, SUM(jami_hisoblangan - avans_jami) as jami')
                ->groupBy('xodim_id')
                ->pluck('jami', 'xodim_id');

            $oldingiQoldiqlar = collect();
            foreach ($xodimlar as $x) {
                $dastlabki = (float) ($x->ishHaqiSozlama->dastlabki_qoldiq ?? 0);
                $oldingiQoldiqlar[$x->id] = $dastlabki + (float) ($eskiHisoblarQoldigi->get($x->id) ?? 0);
            }
        } elseif ($tab === 'tarix') {
            $tarixHisoblar = IshHaqiHisob::with('xodim')
                ->when($filialId, fn($q) => $q->whereHas('xodim', fn($x) => $x->where('filial_id', $filialId)))
                ->when($holat, fn($q) => $q->where('holat', $holat))
                ->when($qidiruv, fn($q) => $q->whereHas('xodim', fn($x) => $x->where('ism_familiya', 'like', "%{$qidiruv}%")))
                ->latest('yil')->latest('oy')->latest('id')
                ->paginate(30)->withQueryString();

            $statistika = [
                'jami'      => IshHaqiHisob::count(),
                'tolandi'   => IshHaqiHisob::where('holat', 'tolandi')->count(),
                'kutilmoqda'=> IshHaqiHisob::where('holat', 'hisoblangan')->count(),
                'bu_oy'     => IshHaqiHisob::where('yil', now()->year)->where('oy', now()->month)->sum('jami_hisoblangan'),
            ];
        } elseif ($tab === 'sozlamalar') {
            $sozlamaXodimlar = Foydalanuvchi::faol()
                ->with('ishHaqiSozlama')
                ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                ->when($qidiruv, fn($q) => $q->where('ism_familiya', 'like', "%{$qidiruv}%"))
                ->orderBy('ism_familiya')->get();

            $damOlishKalendar = DamOlishKuni::shuYilgi($kalendarYil);
        } elseif ($tab === 'dashboard') {
            $statistika = [
                'jami_xodim'   => Foydalanuvchi::faol()->whereHas('ishHaqiSozlama')->count(),
                'bu_oy_jami'   => IshHaqiHisob::where('yil', now()->year)->where('oy', now()->month)->sum('jami_hisoblangan'),
                'bu_oy_tolandi'=> IshHaqiHisob::where('yil', now()->year)->where('oy', now()->month)->where('holat', 'tolandi')->sum('jami_hisoblangan'),
                'bu_oy_kutilmoqda' => IshHaqiHisob::where('yil', now()->year)->where('oy', now()->month)->where('holat', 'hisoblangan')->sum('jami_hisoblangan'),
            ];

            $reyting = IshHaqiHisob::with('xodim')
                ->where('yil', now()->year)->where('oy', now()->month)
                ->orderByDesc('jami_hisoblangan')
                ->take(10)->get();
        }

        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();

        return view('ish_haqi.index', compact(
            'tab', 'xodimlar', 'davomatlar', 'hisoblar', 'sozlamaXodimlar', 'tarixHisoblar',
            'statistika', 'reyting', 'filiallar', 'filialId', 'qidiruv',
            'yil', 'oy', 'holat', 'kunlarSoni', 'oyBoshi', 'oyYopiqmi', 'oldingiQoldiqlar', 'globalSozlama',
            'bonusTurlari', 'shartnomaShablonlari', 'mavjudFoydalanuvchilar', 'manba',
            'kalendarYil', 'damOlishKalendar'
        ));
    }

    /** Butun oylik tabel (davomat) belgilashni saqlash — har xodim, har kun uchun bir yo'la. */
    public function davomatSaqla(Request $request)
    {
        $request->validate([
            'yil'       => 'required|integer',
            'oy'        => 'required|integer|min:1|max:12',
            'holat'     => 'required|array',
            'holat.*'   => 'array',
            'holat.*.*' => 'nullable|in:keldi,kech_qoldi,kelmadi,tatil,kasal,dam_olish',
        ]);

        $yil = (int) $request->yil;
        $oy  = (int) $request->oy;

        if (DavomatOyHolati::yopiqmi($yil, $oy)) {
            return back()->with('xato', "Bu oy allaqachon yopilgan — o'zgartirish mumkin emas.");
        }

        $oyBoshi = Carbon::create($yil, $oy, 1);

        foreach ($request->holat as $xodimId => $kunlar) {
            foreach ($kunlar as $kun => $holat) {
                $sana = $oyBoshi->copy()->day((int) $kun)->toDateString();

                if (empty($holat)) {
                    // Bo'sh (belgilanmagan) qoldirilgan kun — mavjud yozuv bo'lsa o'chiriladi.
                    XodimDavomat::where('xodim_id', (int) $xodimId)->where('sana', $sana)->delete();
                    continue;
                }

                XodimDavomat::updateOrCreate(
                    ['xodim_id' => (int) $xodimId, 'sana' => $sana],
                    ['holat' => $holat, 'belgilagan_id' => Auth::id()]
                );
            }
        }

        return back()->with('muvaffaqiyat', "{$oy}/{$yil} oyi tabeli saqlandi.");
    }

    /** Oyni yopish — shu oydan keyin davomat o'zgartirilmaydi, keyingi oy avtomatik ochiladi. */
    public function oyYopish(Request $request)
    {
        $request->validate(['yil' => 'required|integer', 'oy' => 'required|integer|min:1|max:12']);

        $yil = (int) $request->yil;
        $oy  = (int) $request->oy;

        DavomatOyHolati::updateOrCreate(
            ['yil' => $yil, 'oy' => $oy],
            ['holat' => 'yopiq', 'yopgan_id' => Auth::id(), 'yopilgan_vaqt' => now()]
        );

        $keyingi = Carbon::create($yil, $oy, 1)->addMonth();

        return redirect()->route('ish_haqi.index', [
            'tab' => 'davomat', 'yil' => $keyingi->year, 'oy' => $keyingi->month,
        ])->with('muvaffaqiyat', "{$oy}/{$yil} oyi yopildi. Endi {$keyingi->month}/{$keyingi->year} tabeli ochiq.");
    }

    /** Xato/tasodifan yopilgan oyni qayta ochish — tabel yana tahrirlanadigan bo'ladi. */
    public function oyQaytaOchish(Request $request)
    {
        $request->validate(['yil' => 'required|integer', 'oy' => 'required|integer|min:1|max:12']);

        $yil = (int) $request->yil;
        $oy  = (int) $request->oy;

        DavomatOyHolati::where('yil', $yil)->where('oy', $oy)->update(['holat' => 'ochiq']);

        return back()->with('muvaffaqiyat', "{$oy}/{$yil} oyi qayta ochildi — tabelni yana tahrirlash mumkin.");
    }

    /** Tanlangan oy uchun barcha xodimlarga ish haqini hisoblash (yoki qayta hisoblash). */
    public function hisobla(Request $request)
    {
        $request->validate(['yil' => 'required|integer', 'oy' => 'required|integer|min:1|max:12']);

        $user     = Auth::user();
        $filialId = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;

        $natija = $this->svc->hisoblaBarchasi((int) $request->yil, (int) $request->oy, $filialId);

        return back()->with('muvaffaqiyat', "{$natija['hisoblandi']} ta xodim uchun ish haqi hisoblandi.");
    }

    /** Qo'shimcha hisoblash / ushlanma (avans, jarima) kiritish. */
    public function qoshimchaSaqla(Request $request, IshHaqiHisob $hisob)
    {
        $request->validate([
            'qoshimcha_hisoblash' => 'nullable|numeric|min:0',
            'qoshimcha_izoh'      => 'nullable|string|max:300',
            'ushlanma'            => 'nullable|numeric|min:0',
            'ushlanma_izoh'       => 'nullable|string|max:300',
        ]);

        $this->svc->qoshimchaVaUshlanmaSaqla(
            $hisob,
            (float) ($request->qoshimcha_hisoblash ?? 0),
            $request->qoshimcha_izoh,
            (float) ($request->ushlanma ?? 0),
            $request->ushlanma_izoh
        );

        return back()->with('muvaffaqiyat', 'Qo\'shimcha/ushlanma saqlandi.');
    }

    /** Ish haqini to'lash — Harajatlar moduliga avtomatik yozadi. */
    public function tola(Request $request, IshHaqiHisob $hisob)
    {
        $request->validate(['kassa_turi' => 'required|in:naqd,terminal,bank']);

        try {
            $this->svc->tolash($hisob, $request->kassa_turi);
        } catch (\RuntimeException $e) {
            return back()->with('xato', $e->getMessage());
        }

        return back()->with('muvaffaqiyat', "Ish haqi to'landi va Harajatlar moduliga yozildi.");
    }

    /** Xodim uchun oklad/bonus foizi/oylik reja sozlamalarini saqlash. */
    public function sozlamaSaqla(Request $request, Foydalanuvchi $xodim)
    {
        $request->validate([
            'oklad'            => 'required|numeric|min:0',
            'bonus_foizi'      => 'required|numeric|min:0|max:100',
            'oylik_reja_summa' => 'nullable|numeric|min:0',
            'reja_min_foizi'   => 'nullable|numeric|min:0|max:1000',
            'reja_max_foizi'   => 'nullable|numeric|min:0|max:1000',
            'reja_bonus_summa' => 'nullable|numeric|min:0',
            'soliq_foizi'      => 'nullable|numeric|min:0|max:100',
            'boshqa_ushlanma_foizi' => 'nullable|numeric|min:0|max:100',
            'dastlabki_qoldiq' => 'nullable|numeric',
        ]);

        $rejaMin = (float) ($request->reja_min_foizi ?? 80);
        $rejaMax = (float) ($request->reja_max_foizi ?? 100);
        if ($rejaMax <= $rejaMin) {
            return back()->with('xato', "Maksimal foiz minimal foizdan katta bo'lishi kerak.")->withInput();
        }

        XodimIshHaqiSozlama::updateOrCreate(
            ['xodim_id' => $xodim->id],
            [
                'oklad'            => $request->oklad,
                'bonus_foizi'      => $request->bonus_foizi,
                'oylik_reja_summa' => $request->oylik_reja_summa ?? 0,
                'reja_min_foizi'   => $rejaMin,
                'reja_max_foizi'   => $rejaMax,
                'reja_bonus_summa' => $request->reja_bonus_summa ?? 0,
                // Bo'sh qoldirilsa — global stavka ishlatiladi (NULL saqlanadi).
                'soliq_foizi'          => $request->soliq_foizi !== null && $request->soliq_foizi !== '' ? $request->soliq_foizi : null,
                'boshqa_ushlanma_foizi' => $request->boshqa_ushlanma_foizi !== null && $request->boshqa_ushlanma_foizi !== '' ? $request->boshqa_ushlanma_foizi : null,
                'dastlabki_qoldiq' => $request->dastlabki_qoldiq ?? 0,
            ]
        );

        return back()->with('muvaffaqiyat', "{$xodim->ism_familiya} uchun ish haqi sozlamalari saqlandi.");
    }

    /** Barcha xodimlar uchun standart (global) soliq/ushlanma foizlarini saqlash. */
    public function globalSozlamaSaqla(Request $request)
    {
        $request->validate([
            'soliq_foizi'           => 'required|numeric|min:0|max:100',
            'boshqa_ushlanma_foizi' => 'required|numeric|min:0|max:100',
        ]);

        $global = IshHaqiGlobalSozlama::ol();
        $global->update([
            'soliq_foizi'           => $request->soliq_foizi,
            'boshqa_ushlanma_foizi' => $request->boshqa_ushlanma_foizi,
        ]);

        return back()->with('muvaffaqiyat', 'Global sozlamalar saqlandi.');
    }

    /** Bir yillik dam olish/bayram kunlari kalendarini saqlash (mavjudlarini almashtiradi). */
    public function damOlishSaqla(Request $request)
    {
        $request->validate([
            'yil'     => 'required|integer|min:2000|max:2100',
            'kunlar'  => 'nullable|array',
            'kunlar.*' => 'nullable|in:dam_olish,bayram',
        ]);

        $yil = (int) $request->yil;

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $yil) {
            DamOlishKuni::whereYear('sana', $yil)->delete();

            foreach ($request->input('kunlar', []) as $sana => $turi) {
                if (empty($turi)) {
                    continue;
                }
                DamOlishKuni::create([
                    'sana'          => $sana,
                    'turi'          => $turi,
                    'belgilagan_id' => Auth::id(),
                ]);
            }
        });

        return redirect()->route('ish_haqi.index', ['tab' => 'sozlamalar', 'kalendar_yil' => $yil, 'ochiq_modal' => 'globalSozlamaModal'])
            ->with('muvaffaqiyat', "{$yil}-yil uchun dam olish/bayram kunlari kalendari saqlandi.");
    }

    /** Xodimga avans (oldindan to'lov) berish. */
    public function avansBer(Request $request, Foydalanuvchi $xodim)
    {
        $request->validate([
            'yil'        => 'required|integer',
            'oy'         => 'required|integer|min:1|max:12',
            'summa'      => 'required|numeric|min:1',
            'kassa_turi' => 'required|in:naqd,terminal,bank',
            'izoh'       => 'nullable|string|max:300',
        ]);

        try {
            $this->svc->avansBer(
                $xodim, (int) $request->yil, (int) $request->oy,
                (float) $request->summa, $request->kassa_turi, $request->izoh
            );
        } catch (\RuntimeException $e) {
            return back()->with('xato', $e->getMessage());
        }

        return back()->with('muvaffaqiyat', "{$xodim->ism_familiya}ga avans berildi va Harajatlar moduliga yozildi.");
    }

    // ─── Xodimlar (ro'yxat, ta'til, bonus, shartnoma) ──────────────

    /** Yangi xodim qo'shish — tizimdagi foydalanuvchidan yoki qo'lda kiritish. */
    public function xodimQoshish(Request $request)
    {
        $request->validate([
            'manba'             => 'required|in:tizim,qolda',
            'xodim_id'          => 'required_if:manba,tizim|nullable|integer|exists:foydalanuvchilar,id',
            'ism_familiya'      => 'required_if:manba,qolda|nullable|string|max:200',
            'filial_id'         => 'nullable|integer|exists:filiallar,id',
            'lavozim'           => 'nullable|string|max:150',
            'telefon'           => 'nullable|string|max:20',
            'manzil'            => 'nullable|string|max:300',
            'passport_malumot'  => 'nullable|string|max:100',
            'ishga_kirgan_sana' => 'required|date',
            'oklad'             => 'required|numeric|min:0',
        ]);

        $xodim = $this->xodimSvc->xodimQoshish($request->all());

        return back()->with('muvaffaqiyat', "{$xodim->ism_familiya} xodimlar ro'yxatiga qo'shildi.");
    }

    /** Xodim profilini tahrirlash — lavozim/aloqa/ishga-bo'shash sana/muddatli qo'shimcha. */
    public function xodimTahrirlash(Request $request, Foydalanuvchi $xodim)
    {
        $request->validate([
            'ism_familiya'               => 'nullable|string|max:200',
            'lavozim'                    => 'nullable|string|max:150',
            'telefon'                    => 'nullable|string|max:20',
            'manzil'                     => 'nullable|string|max:300',
            'passport_malumot'           => 'nullable|string|max:100',
            'ishga_kirgan_sana'          => 'nullable|date',
            'ishdan_boshagan_sana'       => 'nullable|date',
            'qoshimcha_ish_haqi'         => 'nullable|numeric|min:0',
            'qoshimcha_boshlanish_sana'  => 'nullable|date',
            'qoshimcha_tugash_sana'      => 'nullable|date|after_or_equal:qoshimcha_boshlanish_sana',
        ]);

        $this->xodimSvc->xodimTahrirlash($xodim, $request->all());

        return back()->with('muvaffaqiyat', "{$xodim->ism_familiya} profili yangilandi.");
    }

    /** Ta'til berish. */
    public function tatilBer(Request $request, Foydalanuvchi $xodim)
    {
        $request->validate([
            'turi'                          => 'required|in:yillik,haq_tolanmaydigan,kasallik,boshqa',
            'boshlanish_sana'               => 'required|date',
            'rejalashtirilgan_qaytish_sana' => 'required|date|after_or_equal:boshlanish_sana',
            'izoh'                          => 'nullable|string|max:300',
        ]);

        $this->xodimSvc->tatilBer($xodim, $request->all());

        return back()->with('muvaffaqiyat', "{$xodim->ism_familiya}ga ta'til berildi.");
    }

    /** Ta'tildan qaytishni belgilash. */
    public function tatilQaytdi(Request $request, XodimTatil $tatil)
    {
        $request->validate(['haqiqiy_qaytgan_sana' => 'nullable|date']);

        $this->xodimSvc->tatilQaytdi($tatil, $request->haqiqiy_qaytgan_sana);

        return back()->with('muvaffaqiyat', "Ta'tildan qaytish belgilandi.");
    }

    /** Hali boshlanmagan ta'tilni bekor qilish. */
    public function tatilBekorQil(XodimTatil $tatil)
    {
        try {
            $this->xodimSvc->tatilBekorQil($tatil);
        } catch (\RuntimeException $e) {
            return back()->with('xato', $e->getMessage());
        }

        return back()->with('muvaffaqiyat', "Ta'til bekor qilindi.");
    }

    /** Xodimga bonus turini biriktirish. */
    public function bonusBiriktirish(Request $request, Foydalanuvchi $xodim)
    {
        $request->validate([
            'bonus_turi_id'  => 'required|integer|exists:bonus_turlari,id',
            'qiymat'         => 'nullable|numeric|min:0',
            'boshlanish_oy'  => 'required|integer|min:1|max:12',
            'boshlanish_yil' => 'required|integer|min:2020|max:2100',
            'tugash_oy'      => 'nullable|integer|min:1|max:12',
            'tugash_yil'     => 'nullable|integer|min:2020|max:2100',
            'izoh'           => 'nullable|string|max:300',
        ]);

        try {
            $this->xodimSvc->bonusBiriktirish($xodim, $request->all());
        } catch (\RuntimeException $e) {
            return back()->with('xato', $e->getMessage())->withInput();
        }

        return back()->with('muvaffaqiyat', "Bonus {$xodim->ism_familiya}ga biriktirildi.");
    }

    /** Biriktirilgan bonusni bekor qilish. */
    public function bonusBekorQil(XodimBonus $bonus)
    {
        $this->xodimSvc->bonusBekorQil($bonus);

        return back()->with('muvaffaqiyat', 'Bonus bekor qilindi.');
    }

    /** Bonus turi (shablon) yaratish/tahrirlash. */
    public function bonusTuriSaqla(Request $request)
    {
        $request->validate([
            'id'              => 'nullable|integer|exists:bonus_turlari,id',
            'nomi'            => 'required|string|max:150',
            'tavsif'          => 'nullable|string|max:300',
            'hisoblash_turi'  => 'required|in:summa,foiz_okladdan',
            'standart_qiymat' => 'nullable|numeric|min:0',
            'holat'           => 'required|in:faol,nofaol',
            'sort_order'      => 'nullable|integer|min:0',
        ]);

        $this->xodimSvc->bonusTuriSaqla($request->all(), $request->id);

        return back()->with('muvaffaqiyat', 'Bonus turi saqlandi.');
    }

    /** Mehnat shartnomasi shabloni yaratish/tahrirlash. */
    public function shartnomaShabloniSaqla(Request $request)
    {
        $request->validate([
            'id'         => 'nullable|integer|exists:mehnat_shartnoma_shablonlari,id',
            'nomi'       => 'required|string|max:150',
            'matn'       => 'required|string',
            'holat'      => 'required|in:faol,nofaol',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $this->xodimSvc->shartnomaShabloniSaqla($request->all(), $request->id);

        return back()->with('muvaffaqiyat', 'Shartnoma shabloni saqlandi.');
    }

    /** Xodim uchun shablondan mehnat shartnomasi yaratish (loyiha). */
    public function shartnomaYarat(Request $request, Foydalanuvchi $xodim)
    {
        $request->validate([
            'shablon_id'              => 'required|integer|exists:mehnat_shartnoma_shablonlari,id',
            'shartnoma_raqami'        => 'nullable|string|max:50',
            'sana'                    => 'nullable|date',
            'amal_qilish_boshlanish'  => 'nullable|date',
            'amal_qilish_tugash'      => 'nullable|date|after_or_equal:amal_qilish_boshlanish',
        ]);

        $shablon = MehnatShartnomaShabloni::findOrFail($request->shablon_id);
        $this->xodimSvc->shartnomaYarat($xodim, $shablon, $request->all());

        return back()->with('muvaffaqiyat', "{$xodim->ism_familiya} uchun shartnoma loyihasi yaratildi.");
    }

    /** Shartnoma matni/rekvizitlarini tahrirlash. */
    public function shartnomaSaqla(Request $request, XodimShartnoma $shartnoma)
    {
        $request->validate([
            'shartnoma_raqami'        => 'nullable|string|max:50',
            'matn'                    => 'required|string',
            'sana'                    => 'required|date',
            'amal_qilish_boshlanish'  => 'nullable|date',
            'amal_qilish_tugash'      => 'nullable|date|after_or_equal:amal_qilish_boshlanish',
        ]);

        $this->xodimSvc->shartnomaSaqla($shartnoma, $request->all());

        return back()->with('muvaffaqiyat', 'Shartnoma yangilandi.');
    }

    /** Shartnoma holatini o'zgartirish (imzolangan / bekor qilingan). */
    public function shartnomaHolatOzgartir(Request $request, XodimShartnoma $shartnoma)
    {
        $request->validate(['holat' => 'required|in:loyiha,imzolangan,bekor_qilingan']);

        $this->xodimSvc->shartnomaHolatOzgartir($shartnoma, $request->holat);

        return back()->with('muvaffaqiyat', 'Shartnoma holati yangilandi.');
    }

    /** Shartnomani PDF ko'rinishida ochish — brauzerda ko'rish va chop etish uchun. */
    public function shartnomaPdf(XodimShartnoma $shartnoma)
    {
        $shartnoma->load('xodim');

        $pdf = Pdf::loadView('ish_haqi.shartnoma_pdf', compact('shartnoma'))->setPaper('A4', 'portrait');

        $fayl = 'mehnat-shartnomasi-' . ($shartnoma->shartnoma_raqami ?: $shartnoma->id) . '.pdf';

        return $pdf->stream($fayl);
    }
}
