<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Mijoz;
use App\Models\Qurilma;
use App\Models\QurilmaLog;
use App\Models\QurilmaProvayder;
use App\Models\QurilmaRozilik;
use App\Models\RegKredit;
use App\Models\TovarKatalog;
use App\Services\DeviceControl\DeviceControlManager;
use App\Services\DeviceControl\DeviceControlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class QurilmaController extends Controller
{
    public function __construct(
        private DeviceControlService $service,
        private DeviceControlManager $manager,
    ) {}

    // ─── Ro'yxat ─────────────────────────────────────────────────
    public function index(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin()
            ? ($request->filial_id ? (int)$request->filial_id : null)
            : (int)$user->filial_id;

        $qurilmalar = Qurilma::with(['mijoz','kredit','tovarKatalog','filial'])
            ->filialda($filialId)
            ->when($request->holat,   fn($q) => $q->where('holat', $request->holat))
            ->when($request->brend,   fn($q) => $q->where('brend', 'like', '%'.$request->brend.'%'))
            ->when($request->qidiruv, fn($q) => $q->where(function($q) use ($request) {
                $q->where('imei1', 'like', '%'.$request->qidiruv.'%')
                  ->orWhere('imei2', 'like', '%'.$request->qidiruv.'%')
                  ->orWhere('model_nomi', 'like', '%'.$request->qidiruv.'%')
                  ->orWhere('serial_raqam', 'like', '%'.$request->qidiruv.'%')
                  ->orWhereHas('mijoz', fn($m) => $m->where('ism', 'like', '%'.$request->qidiruv.'%')
                      ->orWhere('familiya', 'like', '%'.$request->qidiruv.'%'));
            }))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $filiallar   = $user->isAdmin() ? Filial::faol()->get() : collect();
        $holatStat   = Qurilma::filialda($filialId)->selectRaw('holat, COUNT(*) as soni')
            ->groupBy('holat')->pluck('soni', 'holat')->toArray();

        return view('qurilmalar.index', compact('qurilmalar','filiallar','filialId','holatStat'));
    }

    // ─── Yangi qurilma ────────────────────────────────────────────
    public function create()
    {
        $user       = Auth::user();
        $filiallar  = $user->isAdmin() ? Filial::faol()->get() : Filial::where('id', $user->filial_id)->get();
        $kataloglar = TovarKatalog::faol()->with('guruh')->orderBy('nomi')->get();
        return view('qurilmalar.create', compact('filiallar','kataloglar'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validatsiyaQoidalari());

        // IMEI unique tekshiruvi
        foreach (['imei1','imei2','imei3','imei4'] as $slot) {
            if (!empty($validated[$slot])) {
                $this->imeiUniqueTekshir($validated[$slot], null, $slot);
            }
        }

        $qurilma = Qurilma::create(array_merge($validated, [
            'yaratdi_id'     => Auth::id(),
            'yangiladi_id'   => Auth::id(),
            'qoshilgan_sana' => $validated['qoshilgan_sana'] ?? today()->toDateString(),
        ]));

        $this->logYaratildi($qurilma);

        return redirect()->route('qurilmalar.show', $qurilma)
            ->with('muvaffaqiyat', 'Qurilma muvaffaqiyatli qo\'shildi.');
    }

    // ─── Ko'rish ─────────────────────────────────────────────────
    public function show(Qurilma $qurilma)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $qurilma->filial_id !== $user->filial_id) abort(403);

        $qurilma->load([
            'mijoz','kredit.tovarlar','tovarKatalog','filial',
            'provayderUlanishlari.provayder',
            'loglar.xodim','loglar.provayder',
            'roziliklar.mijoz',
            'tasdiqlashlar.soragan',
        ]);
        $provayderlar = QurilmaProvayder::orderBy('sort_order')->get();
        $qoidalar     = \App\Models\QurilmaQoida::faol()->with('provayder')->get();

        return view('qurilmalar.show', compact('qurilma','provayderlar','qoidalar'));
    }

    // ─── Tahrirlash ──────────────────────────────────────────────
    public function edit(Qurilma $qurilma)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $qurilma->filial_id !== $user->filial_id) abort(403);

        $filiallar  = $user->isAdmin() ? Filial::faol()->get() : Filial::where('id', $user->filial_id)->get();
        $kataloglar = TovarKatalog::faol()->with('guruh')->orderBy('nomi')->get();
        return view('qurilmalar.edit', compact('qurilma','filiallar','kataloglar'));
    }

    public function update(Request $request, Qurilma $qurilma)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $qurilma->filial_id !== $user->filial_id) abort(403);

        // IMEI o'zgartirilsa faqat admin qila oladi
        $imeiOzgardi = false;
        foreach (['imei1','imei2','imei3','imei4'] as $slot) {
            $yangi = $request->$slot;
            if ($yangi && $yangi !== $qurilma->$slot) {
                if (!$user->isAdmin()) abort(403, 'IMEI o\'zgartirish faqat admin uchun');
                $imeiOzgardi = true;
            }
        }

        $validated = $request->validate($this->validatsiyaQoidalari($qurilma->id));

        // IMEI unique (o'zini istisno qilib)
        foreach (['imei1','imei2','imei3','imei4'] as $slot) {
            if (!empty($validated[$slot]) && $validated[$slot] !== $qurilma->$slot) {
                $this->imeiUniqueTekshir($validated[$slot], $qurilma->id, $slot);
            }
        }

        $eskiImei = $qurilma->imei1;
        $qurilma->update(array_merge($validated, ['yangiladi_id' => Auth::id()]));

        if ($imeiOzgardi) {
            QurilmaLog::create([
                'qurilma_id' => $qurilma->id,
                'amal'       => QurilmaLog::AMAL_IMEI_CHANGED,
                'holat'      => 'muvaffaqiyat',
                'sabab'      => "Eski IMEI: {$eskiImei} → Yangi: {$qurilma->imei1}",
                'xodim_id'   => Auth::id(),
                'ip_manzil'  => request()->ip(),
            ]);
        }

        return redirect()->route('qurilmalar.show', $qurilma)
            ->with('muvaffaqiyat', 'Qurilma yangilandi.');
    }

    // ─── O'chirish (softDelete) ───────────────────────────────────
    public function destroy(Qurilma $qurilma)
    {
        if (!Auth::user()->isAdmin()) abort(403);
        $qurilma->delete();
        return redirect()->route('qurilmalar.index')
            ->with('muvaffaqiyat', 'Qurilma o\'chirildi.');
    }

    // ─── Shartnomaga biriktirish ──────────────────────────────────
    public function attach(Request $request, Qurilma $qurilma)
    {
        $user = Auth::user();
        if (!$user->isMenejerYoki()) abort(403);

        $request->validate([
            'reg_kredit_id' => 'required|exists:reg_kredit,id',
            'mijoz_id'      => 'required|exists:mijozlar,id',
        ]);

        $eskiKredit = $qurilma->reg_kredit_id;
        $qurilma->update([
            'reg_kredit_id' => $request->reg_kredit_id,
            'mijoz_id'      => $request->mijoz_id,
            'holat'         => Qurilma::HOLAT_SOLD,
            'sotilgan_sana' => today()->toDateString(),
            'yangiladi_id'  => Auth::id(),
        ]);

        QurilmaLog::create([
            'qurilma_id'    => $qurilma->id,
            'reg_kredit_id' => $request->reg_kredit_id,
            'amal'          => QurilmaLog::AMAL_ATTACHED,
            'holat'         => 'muvaffaqiyat',
            'sabab'         => "Shartnoma #{$request->reg_kredit_id} ga biriktirildi",
            'xodim_id'      => Auth::id(),
            'ip_manzil'     => request()->ip(),
        ]);

        return back()->with('muvaffaqiyat', 'Qurilma shartnomaga biriktirildi.');
    }

    // ─── Lock ─────────────────────────────────────────────────────
    public function lock(Request $request, Qurilma $qurilma)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $request->validate([
            'provayder_kod' => 'required|string',
            'sabab'         => 'required|string|max:500',
        ]);

        $javob = $this->service->lock($qurilma, $request->provayder_kod, $request->sabab);

        return back()->with(
            $javob->muvaffaqiyat ? 'muvaffaqiyat' : 'xato',
            $javob->xabar
        );
    }

    // ─── Unlock ───────────────────────────────────────────────────
    public function unlock(Request $request, Qurilma $qurilma)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $request->validate([
            'provayder_kod' => 'required|string',
            'sabab'         => 'nullable|string|max:500',
        ]);

        $javob = $this->service->unlock($qurilma, $request->provayder_kod, $request->sabab ?? '');

        return back()->with(
            $javob->muvaffaqiyat ? 'muvaffaqiyat' : 'xato',
            $javob->xabar
        );
    }

    // ─── Ogohlantirish ───────────────────────────────────────────
    public function warn(Request $request, Qurilma $qurilma)
    {
        if (!Auth::user()->isMenejerYoki()) abort(403);

        $request->validate([
            'provayder_kod' => 'required|string',
            'xabar'         => 'required|string|max:300',
        ]);

        $javob = $this->service->ogohBerish($qurilma, $request->provayder_kod, $request->xabar);

        return back()->with(
            $javob->muvaffaqiyat ? 'muvaffaqiyat' : 'xato',
            $javob->xabar
        );
    }

    // ─── Ozod qilish ─────────────────────────────────────────────
    public function release(Request $request, Qurilma $qurilma)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $provayderKod = $request->provayder_kod ?? 'mock';
        $javob = $this->service->ozodQil($qurilma, $provayderKod);

        return back()->with(
            $javob->muvaffaqiyat ? 'muvaffaqiyat' : 'xato',
            $javob->xabar
        );
    }

    // ─── Loglar ──────────────────────────────────────────────────
    public function logs(Qurilma $qurilma)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $qurilma->filial_id !== $user->filial_id) abort(403);

        $loglar = $qurilma->loglar()->with(['xodim','provayder'])->paginate(30);
        return view('qurilmalar.loglar', compact('qurilma','loglar'));
    }

    // ─── Yordamchi metodlar ───────────────────────────────────────
    private function validatsiyaQoidalari(?int $istisnoId = null): array
    {
        return [
            'tovar_katalog_id' => 'nullable|exists:tovar_katalog,id',
            'filial_id'        => 'required|exists:filiallar,id',
            'brend'            => 'nullable|string|max:100',
            'model_nomi'       => 'required|string|max:200',
            'rang'             => 'nullable|string|max:60',
            'xotira'           => 'nullable|string|max:50',
            'imei1'            => ['nullable','string','size:15','regex:/^\d{15}$/',
                                   Rule::unique('qurilmalar','imei1')->ignore($istisnoId)],
            'imei2'            => ['nullable','string','size:15','regex:/^\d{15}$/',
                                   Rule::unique('qurilmalar','imei2')->ignore($istisnoId)],
            'imei3'            => ['nullable','string','size:15','regex:/^\d{15}$/',
                                   Rule::unique('qurilmalar','imei3')->ignore($istisnoId)],
            'imei4'            => ['nullable','string','size:15','regex:/^\d{15}$/',
                                   Rule::unique('qurilmalar','imei4')->ignore($istisnoId)],
            'serial_raqam'     => 'nullable|string|max:100',
            'holat'            => 'nullable|in:' . implode(',', array_keys(Qurilma::$holatlar)),
            'qoshilgan_sana'   => 'nullable|date',
            'izoh'             => 'nullable|string|max:1000',
        ];
    }

    private function imeiUniqueTekshir(string $imei, ?int $istisnoId, string $slot): void
    {
        $mavjud = Qurilma::withTrashed()->where(function($q) use ($imei) {
            $q->where('imei1', $imei)->orWhere('imei2', $imei)
              ->orWhere('imei3', $imei)->orWhere('imei4', $imei);
        })->when($istisnoId, fn($q) => $q->where('id', '!=', $istisnoId))->exists();

        if ($mavjud) {
            abort(422, "IMEI {$imei} allaqachon ro'yxatda mavjud.");
        }
    }

    private function logYaratildi(Qurilma $qurilma): void
    {
        QurilmaLog::create([
            'qurilma_id' => $qurilma->id,
            'amal'       => QurilmaLog::AMAL_CREATED,
            'holat'      => 'muvaffaqiyat',
            'sabab'      => "Qurilma qo'shildi: {$qurilma->toliq_nomi}",
            'xodim_id'   => Auth::id(),
            'ip_manzil'  => request()->ip(),
        ]);
    }
}
