<?php

namespace App\Http\Controllers;

use App\Models\DavomatOyHolati;
use App\Models\Filial;
use App\Models\Foydalanuvchi;
use App\Models\IshHaqiAvans;
use App\Models\IshHaqiGlobalSozlama;
use App\Models\IshHaqiHisob;
use App\Models\XodimDavomat;
use App\Models\XodimIshHaqiSozlama;
use App\Services\IshHaqiHisoblashService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Xodimlar ish haqi moduli — bitta sahifada tab uslubida:
 * Davomat (tabel) / Hisoblash / Tarix / Sozlamalar / Dashboard.
 */
class IshHaqiController extends Controller
{
    public function __construct(private IshHaqiHisoblashService $svc) {}

    private const TABLAR = ['davomat', 'hisoblash', 'tarix', 'sozlamalar', 'dashboard'];

    public function index(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;
        $tab      = in_array($request->get('tab'), self::TABLAR, true) ? $request->get('tab') : 'davomat';
        $qidiruv  = trim((string) $request->get('qidiruv'));

        $yil         = (int) $request->get('yil', now()->format('Y'));
        $oy          = (int) $request->get('oy', now()->format('n'));
        $holat       = $request->get('holat');

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

        if ($tab === 'davomat') {
            $xodimlar = Foydalanuvchi::faol()
                ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                ->when($qidiruv, fn($q) => $q->where('ism_familiya', 'like', "%{$qidiruv}%"))
                ->orderBy('ism_familiya')->get();

            $oyBoshi    = Carbon::create($yil, $oy, 1)->startOfMonth();
            $kunlarSoni = $oyBoshi->daysInMonth;
            $oyYopiqmi  = DavomatOyHolati::yopiqmi($yil, $oy);

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
            'yil', 'oy', 'holat', 'kunlarSoni', 'oyBoshi', 'oyYopiqmi', 'oldingiQoldiqlar', 'globalSozlama'
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
            'holat.*.*' => 'in:keldi,kech_qoldi,kelmadi,tatil,kasal,dam_olish',
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
}
