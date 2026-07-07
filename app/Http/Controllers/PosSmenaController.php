<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\PosSmena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosSmenaController extends Controller
{
    /** Joriy filial uchun ochiq smenani topadi (bo'lmasa null). */
    public static function joriy(int $filialId): ?PosSmena
    {
        return PosSmena::ochiq()->where('filial_id', $filialId)->latest('ochilgan_vaqt')->first();
    }

    /** Smena ochish formasi. */
    public function ochishForma(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->filial_id ?? Filial::first()->id;

        if (self::joriy($filialId)) {
            return redirect()->route('pos.index');
        }

        // Oxirgi yopilgan smenaning yakuniy qoldig'ini dastlabki qiymat sifatida taklif qilamiz.
        $oxirgiSmena = PosSmena::where('filial_id', $filialId)->where('holat', 'yopiq')
            ->latest('yopilgan_vaqt')->first();
        $taklifQoldiq = $oxirgiSmena->yakuniy_qoldiq ?? 0;

        return view('ombor.pos.smena.ochish', compact('filialId', 'taklifQoldiq'));
    }

    /** Smenani ochish. */
    public function ochish(Request $request)
    {
        $request->validate([
            'dastlabki_qoldiq' => 'required|numeric|min:0',
        ]);

        $user     = Auth::user();
        $filialId = $user->filial_id ?? Filial::first()->id;

        if (self::joriy($filialId)) {
            return redirect()->route('pos.index')->with('xato', 'Bu filialda allaqachon ochiq smena bor.');
        }

        PosSmena::create([
            'smena_raqami'      => PosSmena::yangiSmenaRaqami($filialId),
            'filial_id'         => $filialId,
            'xodim_id'          => Auth::id(),
            'ochilgan_vaqt'     => now(),
            'dastlabki_qoldiq'  => $request->dastlabki_qoldiq,
            'holat'             => 'ochiq',
        ]);

        return redirect()->route('pos.index')->with('muvaffaqiyat', 'Smena ochildi.');
    }

    /** Smena yopish formasi — hisoblangan qoldiqni oldindan ko'rsatadi. */
    public function yopishForma(PosSmena $smena)
    {
        $this->egalikTekshir($smena);

        if ($smena->holat !== 'ochiq') {
            return redirect()->route('pos.index')->with('xato', 'Bu smena allaqachon yopilgan.');
        }

        $hisoblangan = $smena->joriyNaqdQoldiq();
        $sotuvlarSoni = $smena->sotuvlar()->where('holat', 'tugallangan')->count();
        $naqdJami = $smena->sotuvlar()->where('holat', 'tugallangan')->sum('naqd_summa');
        $kartaJami = $smena->sotuvlar()->where('holat', 'tugallangan')->sum('plastik_summa');
        $jamiSavdo = $smena->sotuvlar()->where('holat', 'tugallangan')->sum('jami_tolov');

        return view('ombor.pos.smena.yopish', compact('smena', 'hisoblangan', 'sotuvlarSoni', 'naqdJami', 'kartaJami', 'jamiSavdo'));
    }

    /** Smenani yopish — kassir yakuniy (sanoq) qoldiqni kiritadi, farq hisoblanadi. */
    public function yopish(Request $request, PosSmena $smena)
    {
        $this->egalikTekshir($smena);

        if ($smena->holat !== 'ochiq') {
            return redirect()->route('pos.index')->with('xato', 'Bu smena allaqachon yopilgan.');
        }

        $request->validate([
            'yakuniy_qoldiq' => 'required|numeric|min:0',
            'izoh'           => 'nullable|string|max:500',
        ]);

        $hisoblangan = $smena->joriyNaqdQoldiq();
        $farq = (float) $request->yakuniy_qoldiq - $hisoblangan;

        $smena->update([
            'yopilgan_vaqt'      => now(),
            'hisoblangan_qoldiq' => $hisoblangan,
            'yakuniy_qoldiq'     => $request->yakuniy_qoldiq,
            'farq'               => $farq,
            'holat'              => 'yopiq',
            'izoh'               => $request->izoh,
        ]);

        return redirect()->route('pos.smena.korish', $smena)->with('muvaffaqiyat', 'Smena yopildi.');
    }

    /** Asosiy kassaga topshirish — kutilmoqda holatiga o'tadi. */
    public function topshirish(Request $request, PosSmena $smena)
    {
        $this->egalikTekshir($smena);

        if ($smena->holat !== 'yopiq') {
            return back()->with('xato', "Faqat yopilgan smenadan pul topshirish mumkin.");
        }
        if ($smena->topshirish_holati === 'kutilmoqda') {
            return back()->with('xato', 'Bu smenaning topshirilishi allaqachon kutilmoqda.');
        }

        $request->validate([
            'topshirilgan_summa' => 'required|numeric|min:1',
        ]);

        if ((float) $request->topshirilgan_summa > (float) $smena->yakuniy_qoldiq) {
            return back()->with('xato', "Topshirilayotgan summa smenaning yakuniy qoldig'idan (" . number_format($smena->yakuniy_qoldiq, 0, '.', ' ') . " so'm) oshib ketmasligi kerak.");
        }

        $smena->update([
            'topshirilgan_summa' => $request->topshirilgan_summa,
            'topshirish_holati'  => 'kutilmoqda',
        ]);

        return back()->with('muvaffaqiyat', "Topshirish so'rovi yuborildi — masъul shaxs tasdiqlashini kuting.");
    }

    /** Topshirishni tasdiqlash (admin/menejer). */
    public function topshirishTasdiqlash(PosSmena $smena)
    {
        if ($smena->topshirish_holati !== 'kutilmoqda') {
            return back()->with('xato', "Tasdiqlash uchun kutilayotgan topshirish topilmadi.");
        }

        $smena->update([
            'topshirish_holati' => 'tasdiqlangan',
            'qabul_qilgan_id'   => Auth::id(),
            'qabul_vaqti'       => now(),
        ]);

        return back()->with('muvaffaqiyat', 'Topshirish tasdiqlandi.');
    }

    /** Topshirishni rad etish (admin/menejer) — kassir qayta urinishi mumkin. */
    public function topshirishRad(Request $request, PosSmena $smena)
    {
        $request->validate(['sabab' => 'required|string|min:3|max:500']);

        if ($smena->topshirish_holati !== 'kutilmoqda') {
            return back()->with('xato', "Rad etish uchun kutilayotgan topshirish topilmadi.");
        }

        $smena->update([
            'topshirish_holati' => 'rad_etildi',
            'rad_sababi'        => $request->sabab,
            'qabul_qilgan_id'   => Auth::id(),
            'qabul_vaqti'       => now(),
        ]);

        return back()->with('muvaffaqiyat', 'Topshirish rad etildi.');
    }

    /** Smenalar ro'yxati (tarix/hisobot). */
    public function royxat(Request $request)
    {
        $user      = Auth::user();
        $filialId  = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;
        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();

        $smenalar = PosSmena::with(['filial', 'xodim', 'qabulQilgan'])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->when($request->holat, fn($q) => $q->where('holat', $request->holat))
            ->when($request->dan_sana, fn($q) => $q->whereDate('ochilgan_vaqt', '>=', $request->dan_sana))
            ->when($request->gacha_sana, fn($q) => $q->whereDate('ochilgan_vaqt', '<=', $request->gacha_sana))
            ->orderByDesc('ochilgan_vaqt')->paginate(25)->withQueryString();

        return view('ombor.pos.smena.royxat', compact('smenalar', 'filiallar', 'filialId'));
    }

    /** Bitta smena tafsiloti. */
    public function korish(PosSmena $smena)
    {
        $smena->load(['filial', 'xodim', 'qabulQilgan', 'sotuvlar' => fn($q) => $q->where('holat', 'tugallangan')->latest()]);
        return view('ombor.pos.smena.korish', compact('smena'));
    }

    private function egalikTekshir(PosSmena $smena): void
    {
        $user = Auth::user();
        if ($user->isAdmin() || $user->isMenejerYoki()) return;
        abort_if($smena->xodim_id !== $user->id, 403, "Bu smena boshqa kassirga tegishli.");
    }
}
