<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Foydalanuvchi;
use App\Models\Rol;
use App\Models\Sozlama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Tizimdagi barcha modullar ro'yxati (Ruxsatlar sahifasi + sidebar ko'rinishini boshqaradi).
     * Yangi modul/blok qo'shilganda shu ro'yxatga bitta qator qo'shish kifoya — Ruxsatlar
     * sahifasida avtomatik CRUD jadvali paydo bo'ladi. Sidebar'da esa tegishli blokni
     * @if(Auth::user()->ruxsat('kalit')) bilan o'rab qo'yish kerak (resources/views/layouts/app.blade.php).
     */
    private array $resurslar = [
        'mijozlar'       => ['nomi' => 'Mijozlar',          'icon' => 'people'],
        'kreditlar'      => ['nomi' => 'Shartnomalar',      'icon' => 'file-earmark-text'],
        'tulovlar'       => ['nomi' => "To'lovlar",         'icon' => 'cash-stack'],
        'hisobotlar'     => ['nomi' => 'Hisobotlar',        'icon' => 'bar-chart'],
        'tovarlar'       => ['nomi' => 'Tovarlar',          'icon' => 'box'],
        'ombor'          => ['nomi' => 'Ombor',             'icon' => 'boxes'],
        'taminotchilar'  => ['nomi' => "Ta'minotchilar",    'icon' => 'truck'],
        'harajatlar'     => ['nomi' => 'Harajatlar',        'icon' => 'wallet2'],
        'pul_oqimlari'   => ['nomi' => 'Pul oqimlari',      'icon' => 'arrow-left-right'],
        'malumotnomalar' => ['nomi' => "Ma'lumotnomalar",   'icon' => 'journal-bookmark'],
        'qurilmalar'     => ['nomi' => 'Qurilmalar',        'icon' => 'phone'],
        'xabarnoma'      => ['nomi' => 'Xabarnoma',         'icon' => 'chat-dots'],
        'transferlar'    => ['nomi' => 'Transferlar',       'icon' => 'arrow-left-right'],
        'boshqaruv'      => ['nomi' => 'Boshqaruv',         'icon' => 'gear'],
    ];

    private array $amallar = [
        'korish'     => ['nomi' => "Ko'rish",    'icon' => 'eye',         'rang' => 'primary'],
        'qoshish'    => ['nomi' => "Qo'shish",   'icon' => 'plus-circle', 'rang' => 'success'],
        'tahrirlash' => ['nomi' => 'Tahrirlash', 'icon' => 'pencil',      'rang' => 'warning'],
        'ochirish'   => ['nomi' => "O'chirish",  'icon' => 'trash',       'rang' => 'danger'],
    ];

    // 10 ta tema
    public static array $temalar = [
        1  => ['nomi' => 'Klassik (Qora)',      'sidebar' => '#212529', 'accent' => '#ffc107'],
        2  => ['nomi' => 'Navy (Ko\'k)',        'sidebar' => '#1a2744', 'accent' => '#4dabf7'],
        3  => ['nomi' => 'Yashil',              'sidebar' => '#1a3a2a', 'accent' => '#51cf66'],
        4  => ['nomi' => 'Binafsha',            'sidebar' => '#2d1b69', 'accent' => '#cc5de8'],
        5  => ['nomi' => 'Qizil',               'sidebar' => '#3b1010', 'accent' => '#ff6b6b'],
        6  => ['nomi' => 'Slate (Kulrang)',     'sidebar' => '#1e293b', 'accent' => '#94a3b8'],
        7  => ['nomi' => 'Moviy (Teal)',        'sidebar' => '#0f3640', 'accent' => '#20c997'],
        8  => ['nomi' => 'To\'q To\'q sariq',  'sidebar' => '#2d1f00', 'accent' => '#fd7e14'],
        9  => ['nomi' => 'Qahva',               'sidebar' => '#2c1810', 'accent' => '#a0522d'],
        10 => ['nomi' => 'Midnight (Tun ko\'k)', 'sidebar' => '#0a0f2c', 'accent' => '#4c6ef5'],
    ];

    /** Admin bosh sahifasi */
    public function index()
    {
        $statistika = [
            'foydalanuvchilar' => Foydalanuvchi::count(),
            'faol_users'       => Foydalanuvchi::where('holat', 'faol')->count(),
            'rollar'           => Foydalanuvchi::selectRaw('rol, COUNT(*) as soni')
                                    ->groupBy('rol')->pluck('soni', 'rol'),
        ];
        $sozlamalar = Sozlama::barchasi();

        return view('admin.index', compact('statistika', 'sozlamalar'));
    }

    /** Sozlamalar sahifasi */
    public function sozlamalar()
    {
        $soz    = Sozlama::barchasi();
        $temalar = self::$temalar;
        return view('admin.sozlamalar', compact('soz', 'temalar'));
    }

    /** Sozlamalarni saqlash */
    public function sozlamalarSaqla(Request $request)
    {
        $request->validate([
            'brand_nomi'         => 'required|string|max:50',
            'kompaniya_nomi'     => 'nullable|string|max:200',
            'kompaniya_manzil'   => 'nullable|string|max:300',
            'kompaniya_telefon'  => 'nullable|string|max:100',
            'kompaniya_inn'      => 'nullable|string|max:20',
            'kompaniya_mfo'      => 'nullable|string|max:10',
            'kompaniya_hisob'    => 'nullable|string|max:30',
            'kompaniya_bank'     => 'nullable|string|max:200',
            'kompaniya_direktor' => 'nullable|string|max:200',
            'tema'               => 'required|integer|between:1,10',
            'orqaga_sana_taqiqlansin' => 'nullable|in:0,1',
        ]);

        Sozlama::saqlash($request->only([
            'brand_nomi', 'kompaniya_nomi', 'kompaniya_manzil',
            'kompaniya_telefon', 'kompaniya_inn', 'kompaniya_mfo',
            'kompaniya_hisob', 'kompaniya_bank', 'kompaniya_direktor', 'tema',
            // Hybrid Pochta
            'hybrid_pochta_login', 'hybrid_pochta_password', 'hybrid_pochta_yoqilgan',
            'hybrid_pochta_region_id', 'hybrid_pochta_area_id',
            // Operatsion kun nazorati
            'orqaga_sana_taqiqlansin',
        ]));

        return back()->with('muvaffaqiyat', 'Sozlamalar saqlandi!');
    }

    /**
     * Shartnoma/Kafillik hujjatlari uchun qo'shimcha band matnini yangi versiya
     * sifatida saqlash. Eskisini O'CHIRMAYDI (faqat nofaol qiladi) — shu sababli
     * avval yaratilgan shartnomalar o'zlariga biriktirilgan (snapshot qilingan)
     * eski versiya matnini saqlab qoladi, bu yerdagi o'zgarish ularga ta'sir qilmaydi.
     */
    public function hujjatBandSaqla(Request $request)
    {
        $data = $request->validate([
            'turi' => 'required|in:shartnoma,kafillik',
            'matn' => 'nullable|string|max:5000',
        ]);

        \App\Models\HujjatBand::versiyaSaqlash($data['turi'], $data['matn'] ?? '', $request->user()->id);

        return back()->with('muvaffaqiyat', 'Hujjat matni yangi versiya sifatida saqlandi. Bu faqat shu kundan keyingi yangi shartnomalarga ta\'sir qiladi.');
    }

    /**
     * Shartnoma/Kafillik hujjatining ASOSIY matnini (3-6 bo'lim) saqlash. Bu — JONLI
     * (live) sozlama, versiyalanmaydi — saqlangan zahoti BARCHA shartnomalarga
     * (eski va yangi) qo'llaniladi, chunki PDF har safar shu matndan qayta hosil
     * qilinadi. Qarang: App\Models\HujjatBand::asosiyMatn().
     */
    public function hujjatMatnSaqla(Request $request)
    {
        $data = $request->validate([
            'turi' => 'required|in:shartnoma,kafillik',
            'matn' => 'nullable|string|max:20000',
        ]);

        \App\Models\Sozlama::saqlash([$data['turi'] . '_asosiy_matn' => $data['matn'] ?? '']);

        return back()->with('muvaffaqiyat', 'Hujjatning asosiy matni saqlandi — bu o\'zgarish barcha shartnomalarga (eski va yangi) qo\'llanildi.');
    }

    /** GitHub holati va setup */
    public function github()
    {
        $gitBor     = is_dir(base_path('../.git')) || is_dir(base_path('.git'));
        $gitignore  = file_exists(base_path('.gitignore')) ? file_get_contents(base_path('.gitignore')) : '';
        $gitLog     = [];
        if ($gitBor) {
            exec('git -C ' . base_path() . ' log --oneline -10 2>&1', $gitLog);
        }
        return view('admin.github', compact('gitBor', 'gitLog', 'gitignore'));
    }

    /** Ruxsatlar boshqaruvi */
    public function ruxsatlar()
    {
        $ruxsatlar = DB::table('ruxsatlar')
            ->get()
            ->groupBy('rol')
            ->map(fn($items) => $items->groupBy('resurs')
                ->map(fn($r) => $r->pluck('ruxsat', 'amal')));

        $rollar = Rol::tartibBoyicha()->get();

        return view('admin.ruxsatlar', compact('ruxsatlar', 'rollar'), [
            'resurslar' => $this->resurslar,
            'amallar'   => $this->amallar,
        ]);
    }

    /** Ruxsatlarni saqlash */
    public function ruxsatlarSaqla(Request $request)
    {
        $saqlRollar = Rol::where('kalit', '!=', 'admin')->pluck('kalit');

        foreach ($saqlRollar as $rol) {
            foreach ($this->resurslar as $resurs => $info) {
                foreach ($this->amallar as $amal => $amalInfo) {
                    $key    = "{$rol}_{$resurs}_{$amal}";
                    $ruxsat = $request->has($key) ? 1 : 0;
                    DB::table('ruxsatlar')->updateOrInsert(
                        ['rol' => $rol, 'resurs' => $resurs, 'amal' => $amal],
                        ['ruxsat' => $ruxsat]
                    );
                }
            }
        }

        cache()->forget('ruxsatlar_all');
        return back()->with('muvaffaqiyat', 'Ruxsatlar saqlandi!');
    }


    /** Rollar ro'yxati */
    public function rollar()
    {
        $rollar = Rol::tartibBoyicha()->get()->map(function ($r) {
            $r->foydalanuvchi_soni = Foydalanuvchi::where('rol', $r->kalit)->count();
            return $r;
        });

        return view('admin.rollar', compact('rollar'));
    }

    /** Yangi rol qo'shish (masalan: sotuvchi, yetkazib_beruvchi) */
    public function rollarStore(Request $request)
    {
        $request->validate([
            'kalit' => 'required|string|max:20|alpha_dash|unique:rollar,kalit',
            'nomi'  => 'required|string|max:100',
            'icon'  => 'nullable|string|max:30',
        ], [
            'kalit.alpha_dash' => "Kalit faqat lotin harf, raqam va '_' belgisidan iborat bo'lsin (masalan: sotuvchi).",
            'kalit.unique'     => "Bu kalit allaqachon mavjud.",
        ]);

        $rol = Rol::create([
            'kalit'  => mb_strtolower($request->kalit),
            'nomi'   => $request->nomi,
            'icon'   => $request->icon ?: 'person',
            'tizim'  => false,
            'tartib' => (int) (Rol::max('tartib') ?? 0) + 1,
        ]);

        // Yangi rol uchun barcha modullarga xavfsiz standart: hammasi 0 (ko'rinmaydi)
        $qatorlar = [];
        foreach ($this->resurslar as $resurs => $info) {
            foreach ($this->amallar as $amal => $amalInfo) {
                $qatorlar[] = ['rol' => $rol->kalit, 'resurs' => $resurs, 'amal' => $amal, 'ruxsat' => 0];
            }
        }
        DB::table('ruxsatlar')->insert($qatorlar);
        cache()->forget('ruxsatlar_all');

        return back()->with('muvaffaqiyat', "\"{$rol->nomi}\" roli yaratildi. Endi Ruxsatlar sahifasida unga modul huquqlarini belgilang.");
    }

    /** Rol nomi/ikonkasini tahrirlash (kalit o'zgarmaydi — foydalanuvchilarga bog'langan) */
    public function rollarUpdate(Request $request, Rol $rol)
    {
        $request->validate([
            'nomi' => 'required|string|max:100',
            'icon' => 'nullable|string|max:30',
        ]);

        $rol->update([
            'nomi' => $request->nomi,
            'icon' => $request->icon ?: $rol->icon,
        ]);

        return back()->with('muvaffaqiyat', 'Rol yangilandi.');
    }

    /** Rolni o'chirish (faqat tizim roli bo'lmasa va hech kim foydalanmasa) */
    public function rollarDestroy(Rol $rol)
    {
        if ($rol->tizim) {
            return back()->with('xato', "Tizim rolini o'chirib bo'lmaydi.");
        }

        $ishlatilgan = Foydalanuvchi::where('rol', $rol->kalit)->count();
        if ($ishlatilgan > 0) {
            return back()->with('xato', "Bu rolda {$ishlatilgan} ta foydalanuvchi bor. Avval ularning rolini o'zgartiring.");
        }

        DB::table('ruxsatlar')->where('rol', $rol->kalit)->delete();
        $rolNomi = $rol->nomi;
        $rol->delete();
        cache()->forget('ruxsatlar_all');

        return back()->with('muvaffaqiyat', "\"{$rolNomi}\" roli o'chirildi.");
    }

    /** Foydalanuvchilar ro'yxati + yaratish */
    public function foydalanuvchilar()
    {
        $foydalanuvchilar = Foydalanuvchi::with('filial')
            ->orderBy('rol')->orderBy('ism_familiya')->get();
        $filiallar = Filial::faol()->orderBy('nomi')->get(['id','nomi','kod']);
        $rollar = Rol::tartibBoyicha()->get();

        return view('admin.foydalanuvchilar', compact('foydalanuvchilar', 'filiallar', 'rollar'));
    }

    /** Yangi foydalanuvchi yaratish */
    public function foydalanuvchiStore(Request $request)
    {
        $request->validate([
            'ism_familiya' => 'required|string|max:200',
            'email'        => 'required|email|unique:foydalanuvchilar,email',
            'password'     => 'required|string|min:8|confirmed',
            'rol'          => ['required', Rule::in(Rol::pluck('kalit'))],
            'filial_id'    => 'nullable|exists:filiallar,id',
            'holat'        => 'required|in:faol,nofaol',
        ], [
            'email.unique' => "Bu email allaqachon ro'yxatda bor.",
            'password.min' => "Parol kamida 8 belgi bo'lishi kerak.",
        ]);

        Foydalanuvchi::create([
            'ism_familiya' => $request->ism_familiya,
            'email'        => $request->email,
            'password'     => $request->password, // hashed via cast
            'rol'          => $request->rol,
            'filial_id'    => $request->filial_id ?: null,
            'holat'        => $request->holat,
        ]);

        return back()->with('muvaffaqiyat', 'Foydalanuvchi yaratildi: ' . $request->ism_familiya);
    }

    /** Foydalanuvchi ma'lumotlarini tahrirlash */
    public function foydalanuvchiUpdate(Request $request, Foydalanuvchi $foydalanuvchi)
    {
        $request->validate([
            'ism_familiya' => 'required|string|max:200',
            'email'        => 'required|email|unique:foydalanuvchilar,email,' . $foydalanuvchi->id,
            'rol'          => ['required', Rule::in(Rol::pluck('kalit'))],
            'filial_id'    => 'nullable|exists:filiallar,id',
            'holat'        => 'required|in:faol,nofaol',
        ], [
            'email.unique' => "Bu email allaqachon ro'yxatda bor.",
        ]);

        if ($foydalanuvchi->id === 1 && $request->holat !== 'faol') {
            return back()->with('xato', "Asosiy adminni nofaol qilib bo'lmaydi.");
        }

        $foydalanuvchi->update([
            'ism_familiya' => $request->ism_familiya,
            'email'        => $request->email,
            'rol'          => $request->rol,
            'filial_id'    => $request->filial_id ?: null,
            'holat'        => $request->holat,
        ]);

        return back()->with('muvaffaqiyat', 'Foydalanuvchi yangilandi: ' . $request->ism_familiya);
    }

    /** Foydalanuvchi holatini o'zgartirish (faol/nofaol) */
    public function foydalanuvchiHolat(Request $request, Foydalanuvchi $foydalanuvchi)
    {
        if ($foydalanuvchi->id === 1) {
            return back()->with('xato', "Asosiy admin o'chirib bo'lmaydi.");
        }
        $yangi = $foydalanuvchi->holat === 'faol' ? 'nofaol' : 'faol';
        $foydalanuvchi->update(['holat' => $yangi]);
        return back()->with('muvaffaqiyat', "Foydalanuvchi {$yangi} qilindi.");
    }

    /** Foydalanuvchi parolini reset qilish */
    public function foydalanuvchiParolReset(Request $request, Foydalanuvchi $foydalanuvchi)
    {
        $request->validate([
            'yangi_parol' => 'required|string|min:8|confirmed',
        ]);
        $foydalanuvchi->update(['password' => $request->yangi_parol]);
        return back()->with('muvaffaqiyat', "Parol yangilandi.");
    }
}
