<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Foydalanuvchi;
use App\Models\PosTerminalLog;
use App\Models\PosTolovUsuli;
use App\Models\Sozlama;
use App\Models\TovarGuruh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosTerminalController extends Controller
{
    private const MAX_XATO = 5;
    private const BLOK_DAQIQA = 15;

    /** PIN kirish ekrani — kassir tanlash + raqamli klaviatura. */
    public function pinForma()
    {
        $user = Auth::user();
        $filialId = $user->filial_id ?? Filial::first()->id;

        if (session('pos_terminal.xodim_id') && !session('pos_terminal.qulflangan')) {
            return redirect()->route('terminal.index');
        }

        $kassirlar = Foydalanuvchi::faol()
            ->where(fn($q) => $q->where('filial_id', $filialId)->orWhere('rol', 'admin'))
            ->whereIn('rol', ['admin', 'menejer', 'kassir', 'sotuvchi'])
            ->orderBy('ism_familiya')
            ->get(['id', 'ism_familiya']);

        return view('terminal.pin', compact('kassirlar'));
    }

    /** PIN tekshirish — dastlabki kirish. */
    public function pinTekshir(Request $request)
    {
        $request->validate([
            'xodim_id' => 'required|exists:foydalanuvchilar,id',
            'pin'      => 'required|string|min:4|max:6',
        ]);

        $user     = Auth::user();
        $filialId = $user->filial_id ?? Filial::first()->id;
        $xodim    = Foydalanuvchi::findOrFail($request->xodim_id);

        $natija = $this->pinNiTekshir($xodim, $request->pin, $filialId);
        if (!$natija['muvaffaqiyat']) {
            return response()->json(['xato' => $natija['xato']], 422);
        }

        session(['pos_terminal' => [
            'xodim_id'   => $xodim->id,
            'filial_id'  => $filialId,
            'qulflangan' => false,
            'kirish_vaqt'=> now()->toDateTimeString(),
        ]]);

        PosTerminalLog::yoz('kirish', $xodim->id, $filialId);

        return response()->json(['muvaffaqiyat' => true, 'yonalish' => route('terminal.index')]);
    }

    /** Fullscreen savdo ekrani. */
    public function index()
    {
        $xodimId = session('pos_terminal.xodim_id');
        if (!$xodimId) {
            return redirect()->route('terminal.pin-forma');
        }

        $user     = Auth::user();
        $filialId = $user->filial_id ?? Filial::first()->id;

        $smena = PosSmenaController::joriy($filialId);
        if (!$smena) {
            return redirect()->route('pos.smena.ochish-forma')
                ->with('xato', "Fullscreen terminalda ishlash uchun avval smena ochilishi kerak.");
        }

        $guruhlar = TovarGuruh::faol()
            ->withCount(['tovarlar' => fn($q) => $q->where('holat', 'faol')->where('qoldiq', '>', 0)])
            ->orderBy('nomi')->get();

        $autoLockDaqiqa = (int) Sozlama::ol('pos_auto_lock_daqiqa', '10');
        $kassir = Foydalanuvchi::find($xodimId);
        $tolovUsullari = PosTolovUsuli::faol()->where('filial_id', $filialId)->orderBy('tartib')->orderBy('nomi')->get();

        if (session('pos_terminal.qulflangan')) {
            session(['pos_terminal.qulflangan' => false]);
        }

        return view('terminal.sotish', compact('smena', 'guruhlar', 'autoLockDaqiqa', 'kassir', 'tolovUsullari'));
    }

    /** Ekranni qulflash. */
    public function qulflash(Request $request)
    {
        if (!session('pos_terminal.xodim_id')) {
            return response()->json(['xato' => 'Sessiya topilmadi'], 401);
        }

        session(['pos_terminal.qulflangan' => true]);
        PosTerminalLog::yoz('qulflash', session('pos_terminal.xodim_id'), session('pos_terminal.filial_id'));

        return response()->json(['muvaffaqiyat' => true]);
    }

    /** Qulfni yechish — PIN qayta kiritiladi (o'sha kassir yoki boshqasi). */
    public function yechish(Request $request)
    {
        $request->validate(['pin' => 'required|string|min:4|max:6']);

        $filialId = session('pos_terminal.filial_id');
        if (!$filialId) {
            return response()->json(['xato' => 'Sessiya topilmadi'], 401);
        }

        $joriyXodimId = session('pos_terminal.xodim_id');

        $nomzodlar = Foydalanuvchi::faol()
            ->where(fn($q) => $q->where('filial_id', $filialId)->orWhere('rol', 'admin'))
            ->whereIn('rol', ['admin', 'menejer', 'kassir', 'sotuvchi'])
            ->whereNotNull('pin_kod')
            ->get();

        $topilgan = null;
        foreach ($nomzodlar as $nomzod) {
            if ($nomzod->pinBloklanganmi()) {
                continue;
            }
            if ($nomzod->pinTogri($request->pin)) {
                $topilgan = $nomzod;
                break;
            }
        }

        if (!$topilgan) {
            // Xato urinishni joriy kassirning hisobiga yozamiz (bloklash shu foydalanuvchiga tegishli bo'lsin).
            $joriy = $joriyXodimId ? Foydalanuvchi::find($joriyXodimId) : null;
            if ($joriy) {
                $this->xatoUrinishniQayd($joriy);
            }
            PosTerminalLog::yoz('xato_pin', $joriyXodimId, $filialId);
            return response()->json(['xato' => "PIN noto'g'ri"], 422);
        }

        $topilgan->update(['pin_xato_soni' => 0, 'pin_bloklangan_gacha' => null]);

        $boshqaKassir = $topilgan->id !== $joriyXodimId;

        session(['pos_terminal' => [
            'xodim_id'   => $topilgan->id,
            'filial_id'  => $filialId,
            'qulflangan' => false,
            'kirish_vaqt'=> now()->toDateTimeString(),
        ]]);

        PosTerminalLog::yoz('yechish', $topilgan->id, $filialId);

        return response()->json([
            'muvaffaqiyat'   => true,
            'boshqa_kassir'  => $boshqaKassir,
            'kassir_nomi'    => $topilgan->ism_familiya,
        ]);
    }

    /** Terminaldan to'liq chiqish. */
    public function chiqish()
    {
        $xodimId  = session('pos_terminal.xodim_id');
        $filialId = session('pos_terminal.filial_id');

        if ($xodimId) {
            PosTerminalLog::yoz('chiqish', $xodimId, $filialId);
        }

        session()->forget('pos_terminal');

        return redirect()->route('terminal.pin-forma');
    }

    // ─── Yordamchi metodlar ─────────────────────────────────────────

    private function pinNiTekshir(Foydalanuvchi $xodim, string $pin, int $filialId): array
    {
        if (!$xodim->pin_kod) {
            return ['muvaffaqiyat' => false, 'xato' => "Bu foydalanuvchi uchun PIN kod o'rnatilmagan. Administratorga murojaat qiling."];
        }

        if ($xodim->pinBloklanganmi()) {
            $qoldi = now()->diffInMinutes($xodim->pin_bloklangan_gacha);
            return ['muvaffaqiyat' => false, 'xato' => "Ko'p noto'g'ri urinish sababli bloklangan. {$qoldi} daqiqadan so'ng qayta urining."];
        }

        if (!$xodim->pinTogri($pin)) {
            $this->xatoUrinishniQayd($xodim);
            PosTerminalLog::yoz('xato_pin', $xodim->id, $filialId);
            return ['muvaffaqiyat' => false, 'xato' => "PIN noto'g'ri"];
        }

        return ['muvaffaqiyat' => true];
    }

    private function xatoUrinishniQayd(Foydalanuvchi $xodim): void
    {
        $xodim->increment('pin_xato_soni');
        if ($xodim->pin_xato_soni >= self::MAX_XATO) {
            $xodim->update(['pin_bloklangan_gacha' => now()->addMinutes(self::BLOK_DAQIQA)]);
            PosTerminalLog::yoz('bloklandi', $xodim->id, $xodim->filial_id);
        }
    }
}
