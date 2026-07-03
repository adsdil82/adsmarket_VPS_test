<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HisobotController;
use App\Http\Controllers\MijozController;
use App\Http\Controllers\OmborController;
use App\Http\Controllers\BarcodeLabelController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\LitsenziyaController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\TransferHubController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\KassaTransferController;
use App\Http\Controllers\ContractReassignController;
use App\Http\Controllers\SupplierReturnController;
use App\Http\Controllers\PaymentTypeController;
use App\Http\Controllers\TovarGuruhController;
use App\Http\Controllers\TovarKatalogController;
use App\Http\Controllers\KirimController;
use App\Http\Controllers\ChiqimController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\RegKreditController;
use App\Http\Controllers\TulovController;
use App\Http\Controllers\TilController;
use App\Http\Controllers\TaminotchiController;
use App\Http\Controllers\VersionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\EmailNotificationController;
use App\Http\Controllers\HybridMailController;
use App\Http\Controllers\HisobRejasiController;
use App\Http\Controllers\YangiTulovTuriController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Controllers\HarajatController;
use App\Http\Controllers\PulOqimController;
use App\Http\Controllers\PLController;
use App\Http\Controllers\BLController;
use App\Http\Controllers\QurilmaController;
use App\Http\Controllers\QurilmaProvayderController;
use App\Http\Controllers\MalumotnomalarController;
use App\Http\Controllers\FilialController;
use App\Http\Controllers\KassaController;
use App\Http\Controllers\BirlikController;
use App\Http\Controllers\HarajatTuriController;
use App\Http\Controllers\PulKategoriyaController;
use App\Http\Controllers\BrendController;
use App\Http\Controllers\ValyutaController;
use App\Http\Controllers\TashkilotRekvizitController;
use App\Http\Controllers\ShartnomRekvizitController;
use App\Http\Controllers\StatusSababController;
use App\Http\Controllers\GibridPochtaController;
use App\Http\Controllers\PochtaShablonController;
use App\Http\Controllers\PochXatController;
use App\Http\Controllers\ViloyatController;

// ─── Autentifikatsiya ─────────────────────────────────────────────

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    // throttle:5,1 = 5 urinish, 1 daqiqa bloklash (brute-force himoya)
    Route::post('/login', [AuthController::class, 'login'])
         ->middleware('throttle:5,1')
         ->name('login.post');
});

Route::middleware('auth')->group(function () {
    // POST — form submit orqali chiqish (asosiy yo'l)
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    // GET — brauzer URL orqali to'g'ridan /logout bosylsa ham ishlaydi
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');
    Route::get('/profil', [AuthController::class, 'profil'])->name('profil');
    Route::post('/profil/parol', [AuthController::class, 'parolOzgartirish'])->name('profil.parol');
});

// ─── Asosiy sahifalar (autentifikatsiya talab qilinadi) ───────────

Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])
        ->middleware('litsenziya.tekshir:dashboard')
        ->name('dashboard');
    Route::get('/dashboard/statistika', [DashboardController::class, 'ajaxStatistika'])
        ->name('dashboard.statistika');

    // ─── Mijozlar ─────────────────────────────────────────────────
    Route::prefix('mijozlar')->name('mijozlar.')->group(function () {
        Route::get('/',           [MijozController::class, 'index'])->name('index');
        Route::get('/yangi',      [MijozController::class, 'create'])
            ->middleware(['rol.check:admin,menejer', 'litsenziya.tekshir:mijoz', 'litsenziya.limit:mijoz_max'])
            ->name('create');
        Route::post('/',          [MijozController::class, 'store'])
            ->middleware(['litsenziya.tekshir:mijoz', 'litsenziya.limit:mijoz_max'])
            ->name('store');
        Route::get('/ajax-qidiruv', [MijozController::class, 'ajaxQidiruv'])->name('ajax.qidiruv');
        Route::get('/{mijoz}',    [MijozController::class, 'show'])->name('show');
        Route::get('/{mijoz}/tahrirlash', [MijozController::class, 'edit'])
            ->middleware('rol.check:admin,menejer')
            ->name('edit');
        Route::put('/{mijoz}',    [MijozController::class, 'update'])->name('update');
    });

    // ─── Nasiya shartnomalar ──────────────────────────────────────
    Route::prefix('kreditlar')->name('kreditlar.')->group(function () {
        Route::get('/',          [RegKreditController::class, 'index'])->name('index');
        Route::get('/excel',     [RegKreditController::class, 'excel'])->name('excel');
        Route::get('/ajax/tovar-barkod', [RegKreditController::class, 'tovarBarkod'])->name('ajax.tovar_barkod');
        Route::get('/yangi',     [RegKreditController::class, 'create'])
            ->middleware(['rol.check:admin,menejer', 'litsenziya.tekshir:shartnoma', 'litsenziya.limit:shartnoma_max'])
            ->name('create');
        Route::post('/',         [RegKreditController::class, 'store'])
            ->middleware(['rol.check:admin,menejer', 'litsenziya.tekshir:shartnoma', 'litsenziya.limit:shartnoma_max'])
            ->name('store');
        Route::get('/{kredit}',  [RegKreditController::class, 'show'])->name('show');
        // Hybrid Pochta xat yuborish (Phase 3)
        Route::prefix('{kredit}/pochta')->name('pochta.')->middleware('rol.check:admin,menejer')->group(function () {
            Route::post('/create',  [PochXatController::class, 'create'])->name('create');
            Route::post('/send',    [PochXatController::class, 'send'])->name('send');
            Route::get('/preview',  [PochXatController::class, 'preview'])->name('preview');
        });
        // SMS yuborish (shartnoma sahifasidan, AJAX)
        Route::post('/{kredit}/sms-yubor', [RegKreditController::class, 'smsYubor'])
            ->middleware('rol.check:admin,menejer,kassir')
            ->name('sms.yubor');
        Route::get('/{kredit}/tahrirlash', [RegKreditController::class, 'edit'])
            ->middleware('rol.check:admin,menejer')
            ->name('edit');
        Route::put('/{kredit}',  [RegKreditController::class, 'update'])
            ->middleware('rol.check:admin,menejer')
            ->name('update');
        Route::post('/{kredit}/activate', [RegKreditController::class, 'activate'])
            ->middleware('rol.check:admin,menejer')
            ->name('activate');
        Route::get('/{kredit}/pdf', [RegKreditController::class, 'pdf'])->name('pdf');
        Route::get('/{kredit}/hujjat/{tur}', [RegKreditController::class, 'hujjat'])->name('hujjat');
        Route::get('/{kredit}/hujjat-html/{tur}', [RegKreditController::class, 'hujjatHtml'])->name('hujjat.html');

        // To'lovlar
        Route::get('/{kredit}/tulov',          [TulovController::class, 'create'])
            ->middleware('rol.check:admin,menejer,kassir')
            ->name('tulov.create');
        Route::post('/{kredit}/tulov',         [TulovController::class, 'store'])
            ->middleware('rol.check:admin,menejer,kassir')
            ->name('tulov.store');
        Route::post('/{kredit}/oldin-tulov',   [TulovController::class, 'oldinStore'])
            ->middleware('rol.check:admin,menejer,kassir')
            ->name('tulov.oldin-store');
        Route::get('/{kredit}/qoldiq',         [TulovController::class, 'ajaxQoldiq'])
            ->name('tulov.qoldiq');
        Route::get('/{kredit}/tulov/{tulov}/kvitansiya', [TulovController::class, 'kvitansiya'])
            ->name('tulov.kvitansiya');
        Route::get('/{kredit}/tulov/{tulov}/tahrirlash', [TulovController::class, 'edit'])
            ->middleware('rol.check:admin,menejer')
            ->name('tulov.edit');
        Route::put('/{kredit}/tulov/{tulov}', [TulovController::class, 'update'])
            ->middleware('rol.check:admin,menejer')
            ->name('tulov.update');
        Route::delete('/{kredit}/tulov/{tulov}', [TulovController::class, 'destroy'])
            ->middleware('rol.check:admin')
            ->name('tulov.destroy');

        // Versiyalar
        Route::get('/{kredit}/versiyalar',          [VersionController::class, 'index'])->name('versiyalar.index');
        Route::get('/{kredit}/versiyalar/{versiya}', [VersionController::class, 'show'])->name('versiyalar.show');
        Route::post('/{kredit}/versiyalar/{versiya}/qaytarish', [RegKreditController::class, 'versiyaniQaytar'])->name('versiyalar.qaytarish');
    });

    // ─── Hisobotlar ───────────────────────────────────────────────
    Route::prefix('hisobotlar')->name('hisobotlar.')->middleware('litsenziya.tekshir:hisobot')->group(function () {
        Route::get('/',                    [HisobotController::class, 'index'])->name('index');
        Route::get('/kelayotgan',          [HisobotController::class, 'kelayotganTulovlar'])->name('kelayotgan');
        Route::get('/kredit-portfolio',    [HisobotController::class, 'kreditPortfeli'])->name('kredit_portfolio');
        Route::get('/chiqarilgan',         [HisobotController::class, 'chiqarilganKreditlar'])->name('chiqarilgan');
        Route::get('/sotilgan-tovarlar',   [HisobotController::class, 'sotilganTovarlar'])->name('sotilgan_tovarlar');
        Route::get('/kechikish-analiz',    [HisobotController::class, 'kechikishAnaliz'])->middleware('litsenziya.limit:hisobot_advanced')->name('kechikish_analiz');
        Route::get('/konstruktor',         [HisobotController::class, 'konstruktor'])->middleware('litsenziya.limit:hisobot_advanced')->name('konstruktor');
        Route::post('/konstruktor',        [HisobotController::class, 'konstruktorHisobot'])->middleware('litsenziya.limit:hisobot_advanced')->name('konstruktor.hisobot');
        Route::get('/excel/{tur}',         [HisobotController::class, 'excelExport'])->middleware('litsenziya.limit:hisobot_advanced')->name('excel');
        Route::post('/konstruktor/excel',  [HisobotController::class, 'konstruktorExcel'])->middleware('litsenziya.limit:hisobot_advanced')->name('konstruktor.excel');
        Route::get('/transferlar',            [HisobotController::class, 'transferHisobot'])->name('transfer');
    });

    // ─── Tovar katalog ────────────────────────────────────────────
    Route::prefix('katalog')->name('katalog.')->middleware('rol.check:admin,menejer')->group(function () {
        Route::get('/',               [TovarKatalogController::class, 'index'])->name('index');
        Route::get('/yangi',          [TovarKatalogController::class, 'create'])->middleware(['litsenziya.tekshir:tovar', 'litsenziya.limit:tovar_max'])->name('create');
        Route::post('/',              [TovarKatalogController::class, 'store'])
            ->middleware(['litsenziya.tekshir:tovar', 'litsenziya.limit:tovar_max'])
            ->name('store');
        Route::get('/{katalog}/edit', [TovarKatalogController::class, 'edit'])->name('edit');
        Route::put('/{katalog}',      [TovarKatalogController::class, 'update'])->name('update');
        Route::delete('/{katalog}',   [TovarKatalogController::class, 'destroy'])->name('destroy');
    });

    // ─── Tovar guruhlar ───────────────────────────────────────────
    Route::prefix('tovar-guruhlar')->name('tovar-guruhlar.')->middleware('rol.check:admin,menejer')->group(function () {
        Route::get('/',             [TovarGuruhController::class, 'index'])->name('index');
        Route::post('/',            [TovarGuruhController::class, 'store'])->name('store');
        Route::put('/{guruh}',      [TovarGuruhController::class, 'update'])->name('update');
        Route::delete('/{guruh}',   [TovarGuruhController::class, 'destroy'])->name('destroy');
    });

    // ─── Kirim ────────────────────────────────────────────────────
    Route::prefix('kirim')->name('kirim.')->middleware('rol.check:admin,menejer')->group(function () {
        Route::get('/',         [KirimController::class, 'index'])->name('index');
        Route::get('/yangi',    [KirimController::class, 'create'])->name('create');
        Route::post('/',        [KirimController::class, 'store'])->name('store');
        Route::get('/{kirim}',  [KirimController::class, 'show'])->name('show');
        Route::delete('/{kirim}', [KirimController::class, 'destroy'])->name('destroy');
    });

    // ─── Chiqim ───────────────────────────────────────────────────
    Route::prefix('chiqim')->name('chiqim.')->middleware('rol.check:admin,menejer')->group(function () {
        Route::get('/',          [ChiqimController::class, 'index'])->name('index');
        Route::get('/yangi',     [ChiqimController::class, 'create'])->name('create');
        Route::post('/',         [ChiqimController::class, 'store'])->name('store');
        Route::get('/{chiqim}',  [ChiqimController::class, 'show'])->name('show');
    });

    // ─── POS (Naqd savdo) ─────────────────────────────────────────
    Route::prefix('pos')->name('pos.')->middleware(['litsenziya.tekshir:pos', 'litsenziya.limit:pos'])->group(function () {
        Route::get('/',              [PosController::class, 'index'])->name('index');
        Route::get('/tovarlar',      [PosController::class, 'tovarlar'])->name('tovarlar');
        Route::post('/saqlash',      [PosController::class, 'store'])->name('store');
        Route::get('/tarix',         [PosController::class, 'tarix'])->name('tarix');
        Route::get('/chek/{sotuv}',  [PosController::class, 'chekKorish'])->name('chek');
    });

    // ─── Ombor (eski, endi katalog ishlatiladi) ───────────────────
    Route::prefix('ombor')->name('ombor.')->group(function () {
        Route::get('/',       [OmborController::class, 'index'])->name('index');
        Route::get('/tovar/{tovar}', [OmborController::class, 'tovar'])->name('tovar');
        Route::get('/etiketka',          [BarcodeLabelController::class, 'index'])->name('etiketka');
        Route::get('/etiketka/tovarlar', [BarcodeLabelController::class, 'tovarlar'])->name('etiketka.tovarlar');
        Route::get('/kirim',  fn() => redirect()->route('kirim.index'))->name('kirim');
        Route::get('/chiqim', fn() => redirect()->route('chiqim.index'))->name('chiqim');
    });

    // ─── Transferlar moduli (kengaytirilgan) ─────────────────────────
    Route::prefix('transfer')->name('transfer.')->middleware('auth')->group(function () {

        // Bosh sahifa va audit
        Route::get('/', [TransferHubController::class, 'index'])->name('index');
        Route::get('/audit', [TransferHubController::class, 'auditJurnal'])->name('audit');

        // Tovar transferlari (filiallar/omborlar arasi)
        Route::prefix('tovar')->name('tovar.')->middleware('rol.check:admin,menejer,omborchi')->group(function () {
            Route::get('/',                               [StockTransferController::class, 'index'])->name('index');
            Route::get('/yangi',                          [StockTransferController::class, 'create'])->name('create');
            Route::post('/',                              [StockTransferController::class, 'store'])->name('store');
            Route::get('/{transfer}',                     [StockTransferController::class, 'show'])->name('show');
            Route::post('/{transfer}/qabul',              [StockTransferController::class, 'qabulQilish'])->name('qabul');
            Route::post('/{transfer}/bekor',              [StockTransferController::class, 'bekorQilish'])->name('bekor');
        });

        // Kassa transferlari
        Route::prefix('kassa')->name('kassa.')->middleware('rol.check:admin,menejer,kassir')->group(function () {
            Route::get('/',                               [KassaTransferController::class, 'index'])->name('index');
            Route::get('/yangi',                          [KassaTransferController::class, 'create'])->name('create');
            Route::post('/',                              [KassaTransferController::class, 'store'])->name('store');
            Route::get('/{kassaTransfer}',                [KassaTransferController::class, 'show'])->name('show');
            Route::post('/{kassaTransfer}/qabul',         [KassaTransferController::class, 'qabulQilish'])->name('qabul');
            Route::post('/{kassaTransfer}/bekor',         [KassaTransferController::class, 'bekorQilish'])->name('bekor');
        });

        // Shartnoma qayta tayinlash va filial ko'chirish
        Route::prefix('shartnoma')->name('shartnoma.')->middleware('rol.check:admin,menejer')->group(function () {
            Route::get('/xodim-tarixi',                   [ContractReassignController::class, 'xodimIndex'])->name('xodim_tarixi');
            Route::post('/xodim-tayin',                   [ContractReassignController::class, 'xodimQaytaTayin'])->name('xodim_tayin');
            Route::get('/filial-tarixi',                  [ContractReassignController::class, 'filialIndex'])->name('filial_tarixi');
            Route::post('/filial-kochir',                 [ContractReassignController::class, 'filialKochirish'])->name('filial_kochir');
            // AJAX endpoints (kredit kartochkasidan)
            Route::post('/ajax/{kredit}/xodim-tayin',     [ContractReassignController::class, 'ajaxXodimTayin'])->name('ajax.xodim');
            Route::post('/ajax/{kredit}/filial-kochir',   [ContractReassignController::class, 'ajaxFilialKochir'])->name('ajax.filial');
        });

        // Ta'minotchiga qaytarish (Supplier Return)
        Route::prefix('supplier-return')->name('supplier-return.')->middleware('rol.check:admin,menejer,omborchi')->group(function () {
            Route::get('/',                               [SupplierReturnController::class, 'index'])->name('index');
            Route::get('/yangi',                          [SupplierReturnController::class, 'create'])->name('create');
            Route::post('/',                              [SupplierReturnController::class, 'store'])->name('store');
            Route::get('/{supplierReturn}',               [SupplierReturnController::class, 'show'])->name('show');
            Route::post('/{supplierReturn}/tasdiqlash',   [SupplierReturnController::class, 'tasdiqlash'])->name('tasdiqla');
            Route::post('/{supplierReturn}/qaytarildi',   [SupplierReturnController::class, 'qaytarildi'])->name('qaytarildi');
        });

        // To'lov turlari boshqaruvi
        Route::prefix('to-lov-turlari')->name('tolov_turi.')->middleware('rol.check:admin')->group(function () {
            Route::get('/',            [PaymentTypeController::class, 'index'])->name('index');
            Route::post('/',           [PaymentTypeController::class, 'store'])->name('store');
            Route::put('/{tulovTuri}', [PaymentTypeController::class, 'update'])->name('update');
            Route::post('/mapping',    [PaymentTypeController::class, 'mappingStore'])->name('mapping');
        });
    });

    // ─── Admin panel ──────────────────────────────────────────────
    Route::prefix('admin')->name('admin.')->middleware('rol.check:admin')->group(function () {
        Route::get('/',                  [AdminController::class, 'index'])->name('index');
        Route::get('/sozlamalar',        [AdminController::class, 'sozlamalar'])->name('sozlamalar');
        Route::post('/sozlamalar',       [AdminController::class, 'sozlamalarSaqla'])->name('sozlamalar.saqlash');
        Route::post('/hujjat-band', [AdminController::class, 'hujjatBandSaqla'])->name('hujjatband.saqlash');
        Route::post('/hujjat-matn', [AdminController::class, 'hujjatMatnSaqla'])->name('hujjatmatn.saqlash');
        Route::get('/ruxsatlar',         [AdminController::class, 'ruxsatlar'])->name('ruxsatlar');
        Route::post('/ruxsatlar',        [AdminController::class, 'ruxsatlarSaqla'])->name('ruxsatlar.saqlash');
        Route::get('/rollar',            [AdminController::class, 'rollar'])->name('rollar');
        Route::post('/rollar',           [AdminController::class, 'rollarStore'])->name('rollar.store');
        Route::put('/rollar/{rol}',      [AdminController::class, 'rollarUpdate'])->name('rollar.update');
        Route::delete('/rollar/{rol}',   [AdminController::class, 'rollarDestroy'])->name('rollar.destroy');
        Route::put('/rollar/{rol}/tulov-sozlama', [AdminController::class, 'rollarTulovSozlama'])->name('rollar.tulov_sozlama');
        Route::get('/foydalanuvchilar',  [AdminController::class, 'foydalanuvchilar'])->name('foydalanuvchilar');
        Route::post('/foydalanuvchilar', [AdminController::class, 'foydalanuvchiStore'])->name('foydalanuvchilar.store');
        Route::put('/foydalanuvchilar/{foydalanuvchi}', [AdminController::class, 'foydalanuvchiUpdate'])->name('foydalanuvchilar.update');
        Route::post('/foydalanuvchilar/{foydalanuvchi}/holat', [AdminController::class, 'foydalanuvchiHolat'])->name('foydalanuvchilar.holat');
        Route::post('/foydalanuvchilar/{foydalanuvchi}/parol', [AdminController::class, 'foydalanuvchiParolReset'])->name('foydalanuvchilar.parol');
        Route::get('/audit',             [AuditController::class, 'index'])->name('audit');
        Route::get('/deploy',            [BackupController::class, 'deploy'])->name('deploy');
        Route::get('/deploy/db-zip',     [BackupController::class, 'dbZip'])->name('deploy.db');
        Route::get('/deploy/app-zip',    [BackupController::class, 'appZip'])->name('deploy.app');
        Route::get('/deploy/progress/{turi}', [BackupController::class, 'progress'])->name('deploy.progress');
        Route::get('/litsenziya',        [LitsenziyaController::class, 'index'])->name('litsenziya');
        Route::post('/litsenziya',       [LitsenziyaController::class, 'faollashtir'])->name('litsenziya.faollashtir');
        Route::get('/github',            [AdminController::class, 'github'])->name('github');
        // Hybrid Pochta (hybrid.pochta.uz) — jismoniy pochta xatlari
        Route::prefix('gibrid-pochta')->name('gibrid-pochta.')->group(function () {
            Route::post('/test-connection', [GibridPochtaController::class, 'testConnection'])->name('test-connection');
            Route::get('/regions',          [GibridPochtaController::class, 'regions'])->name('regions');
            Route::get('/areas',            [GibridPochtaController::class, 'areas'])->name('areas');
            Route::get('/loglar',           [GibridPochtaController::class, 'loglar'])->name('pochta-loglar.index');
            Route::get('/kvitansiya/{log}',      [GibridPochtaController::class, 'kvitansiya'])->name('kvitansiya');
            Route::post('/qayta-yuborish/{log}', [GibridPochtaController::class, 'qaytaYuborish'])->name('qayta-yuborish');
        });
        // Xabarnoma sozlamalari (admin panel dan)
        Route::post('/notif/sms',      [SmsController::class, 'sozlamalarSaqla'])->name('notif.sms.saqlash');
        Route::post('/notif/telegram', [TelegramController::class, 'sozlamalarSaqla'])->name('notif.telegram.saqlash');
        Route::post('/notif/email',    [EmailNotificationController::class, 'sozlamalarSaqla'])->name('notif.email.saqlash');
        Route::post('/notif/hybrid',   [HybridMailController::class, 'sozlamalarSaqla'])->name('notif.hybrid.saqlash');
    });

    // ─── Ta'minotchilar moduli ─────────────────────────────────────
    Route::prefix('taminotchi')->name('taminotchi.')->middleware('auth')->group(function () {
        // Asosiy CRUD (menejer + admin + omborchi)
        Route::get('/',               [TaminotchiController::class, 'index'])->name('index');
        Route::get('/yangi',          [TaminotchiController::class, 'create'])->name('create')
            ->middleware('rol.check:admin,menejer');
        Route::post('/',              [TaminotchiController::class, 'store'])->name('store')
            ->middleware('rol.check:admin,menejer');
        Route::get('/{taminotchi}',   [TaminotchiController::class, 'show'])->name('show');
        Route::get('/{taminotchi}/edit', [TaminotchiController::class, 'edit'])->name('edit')
            ->middleware('rol.check:admin,menejer');
        Route::put('/{taminotchi}',   [TaminotchiController::class, 'update'])->name('update')
            ->middleware('rol.check:admin,menejer');

        // Kirim (omborchi + admin + menejer)
        Route::get('/{taminotchi}/kirim/yangi', [TaminotchiController::class, 'kirimCreate'])->name('kirim.create')
            ->middleware('rol.check:admin,menejer,omborchi');
        Route::post('/{taminotchi}/kirim',      [TaminotchiController::class, 'kirimStore'])->name('kirim.store')
            ->middleware('rol.check:admin,menejer,omborchi');
        Route::get('/{taminotchi}/kirim/{kirim}/tahrirlash', [TaminotchiController::class, 'kirimEdit'])->name('kirim.edit')
            ->middleware('rol.check:admin,menejer,omborchi');
        Route::put('/{taminotchi}/kirim/{kirim}', [TaminotchiController::class, 'kirimUpdate'])->name('kirim.update')
            ->middleware('rol.check:admin,menejer,omborchi');

        // To'lov (kassir + admin + menejer)
        Route::post('/{taminotchi}/tulov', [TaminotchiController::class, 'tulovStore'])->name('tulov.store')
            ->middleware('rol.check:admin,menejer,kassir');
        Route::delete('/{taminotchi}/tulov/{tulov}', [TaminotchiController::class, 'tulovDestroy'])->name('tulov.destroy')
            ->middleware('rol.check:admin');
        Route::put('/{taminotchi}/tulov/{tulov}',    [TaminotchiController::class, 'tulovUpdate'])->name('tulov.update')
            ->middleware('rol.check:admin');

        // Akt sverka va hisobotlar (barcha login bo'lganlar)
        Route::get('/{taminotchi}/akt-sverka', [TaminotchiController::class, 'aktSverka'])->name('akt_sverka');
        Route::get('/hisobot/reestr',           [TaminotchiController::class, 'tulovReestr'])->name('tulov_reestr');
        Route::get('/hisobot/kirim-reestr',     [TaminotchiController::class, 'kirimReestr'])->name('kirim_reestr');
        Route::get('/hisobot/balans',           [TaminotchiController::class, 'hisobot'])->name('hisobot');
    });

    // Til o'zgartirish
    Route::post('/til', [TilController::class, 'ozgartir'])->name('til.ozgartir');

    // ─── Xabarnoma moduli ─────────────────────────────────────────
    Route::prefix('xabarnoma')->name('xabarnoma.')->middleware('auth')->group(function () {

        // SMS
        Route::prefix('sms')->name('sms.')->middleware('rol.check:admin,menejer')->group(function () {
            Route::get('/',            [SmsController::class, 'guruhli'])->name('index');
            Route::get('/guruhli',     [SmsController::class, 'guruhli'])->name('guruhli');
            Route::post('/guruhli',    [SmsController::class, 'guruhliSend'])->name('guruhli.send');
            Route::post('/preview',    [SmsController::class, 'preview'])->name('preview');
            Route::get('/yakka',       [SmsController::class, 'yakka'])->name('yakka');
            Route::post('/yakka',      [SmsController::class, 'yakkaSend'])->name('yakka.send');
            Route::get('/tarix',       [SmsController::class, 'tarix'])->name('tarix');
            Route::get('/sozlamalar',  [SmsController::class, 'sozlamalar'])->name('sozlamalar');
            Route::post('/sozlamalar', [SmsController::class, 'sozlamalarSaqla'])->name('sozlamalar.saqlash');
            Route::post('/test',       [SmsController::class, 'testSms'])->name('test');
        });

        // Telegram
        Route::prefix('telegram')->name('telegram.')->middleware('rol.check:admin,menejer')->group(function () {
            Route::get('/',            [TelegramController::class, 'index'])->name('index');
            Route::post('/sozlamalar', [TelegramController::class, 'sozlamalarSaqla'])->name('sozlamalar.saqlash');
            Route::post('/test',       [TelegramController::class, 'testTelegram'])->name('test');
        });

        // Email
        Route::prefix('email')->name('email.')->middleware('rol.check:admin,menejer')->group(function () {
            Route::get('/',            [EmailNotificationController::class, 'index'])->name('index');
            Route::post('/sozlamalar', [EmailNotificationController::class, 'sozlamalarSaqla'])->name('sozlamalar.saqlash');
            Route::post('/test',       [EmailNotificationController::class, 'testEmail'])->name('test');
        });

        // Gibrid Pochta
        Route::prefix('hybrid-mail')->name('hybrid_mail.')->middleware('rol.check:admin,menejer')->group(function () {
            Route::get('/',            [HybridMailController::class, 'index'])->name('index');
            Route::post('/sozlamalar', [HybridMailController::class, 'sozlamalarSaqla'])->name('sozlamalar.saqlash');
            Route::post('/test',       [HybridMailController::class, 'testSend'])->name('test');
        });

        // Shablonlar
        Route::prefix('shablonlar')->name('shablonlar.')->middleware('rol.check:admin,menejer')->group(function () {
            Route::get('/',                  [NotificationTemplateController::class, 'index'])->name('index');
            Route::get('/yangi',             [NotificationTemplateController::class, 'create'])->name('create');
            Route::post('/',                 [NotificationTemplateController::class, 'store'])->name('store');
            Route::get('/{shablon}/tahrir',  [NotificationTemplateController::class, 'edit'])->name('edit');
            Route::put('/{shablon}',         [NotificationTemplateController::class, 'update'])->name('update');
            Route::post('/{shablon}/preview',[NotificationTemplateController::class, 'preview'])->name('preview');
        });

    });


    // ─── Harajatlar ───────────────────────────────────────────────

    // Pul Oqimlari (CashFlow)
    Route::prefix('pul-oqimlari')->name('pul-oqimlari.')->group(function () {
        Route::get('/',                          [PulOqimController::class, 'index'])->name('index');
        Route::get('/hisobot',                    [PulOqimController::class, 'hisobot'])->name('hisobot');
        Route::get('/yangi',                     [PulOqimController::class, 'create'])->name('create')
            ->middleware('rol.check:admin,menejer,kassir');
        Route::post('/',                         [PulOqimController::class, 'store'])->name('store')
            ->middleware('rol.check:admin,menejer,kassir');
        Route::get('/{pulOqim}/tahrirlash',      [PulOqimController::class, 'edit'])->name('edit')
            ->middleware('rol.check:admin,menejer');
        Route::put('/{pulOqim}',                 [PulOqimController::class, 'update'])->name('update')
            ->middleware('rol.check:admin,menejer');
        Route::delete('/{pulOqim}',              [PulOqimController::class, 'destroy'])->name('destroy')
            ->middleware('rol.check:admin');
        Route::get('/ajax/kunlik-chart',         [PulOqimController::class, 'ajaxKunlikChart'])->name('ajax.chart');

        Route::prefix('moliyaviy-natija')->name('moliyaviy-natija.')->middleware('rol.check:admin,menejer,hisobchi')->group(function () {
            Route::get('/',              [PLController::class, 'index'])->name('index');
            Route::post('/qiymat',       [PLController::class, 'qiymatSaqlash'])->name('qiymat');
        });

        Route::prefix('balans')->name('balans.')->middleware('rol.check:admin,menejer,hisobchi')->group(function () {
            Route::get('/',              [BLController::class, 'index'])->name('index');
            Route::post('/qiymat',       [BLController::class, 'qiymatSaqlash'])->name('qiymat');
            Route::post('/modda',        [BLController::class, 'qatorStore'])->name('modda.store');
            Route::delete('/modda/{qator}', [BLController::class, 'qatorDestroy'])->name('modda.destroy');
            Route::put('/modda/{qator}', [BLController::class, 'qatorUpdate'])->name('modda.update');
        });
    });

    Route::prefix('harajatlar')->name('harajatlar.')->group(function () {
        Route::get('/',               [HarajatController::class, 'index'])->name('index');
        Route::get('/yangi',          [HarajatController::class, 'create'])->name('create')
            ->middleware('rol.check:admin,menejer');
        Route::post('/',              [HarajatController::class, 'store'])->name('store')
            ->middleware('rol.check:admin,menejer');
        Route::get('/{harajat}/tahrirlash', [HarajatController::class, 'edit'])->name('edit')
            ->middleware('rol.check:admin,menejer');
        Route::put('/{harajat}',      [HarajatController::class, 'update'])->name('update')
            ->middleware('rol.check:admin,menejer');
        Route::delete('/{harajat}',   [HarajatController::class, 'destroy'])->name('destroy')
            ->middleware('rol.check:admin');
    });


    // Buxgalteriya
    Route::prefix('buxgalteriya')->name('buxgalteriya.')->middleware('rol.check:admin')->group(function () {
        Route::get('/hisoblar',            [HisobRejasiController::class, 'index'])->name('hisoblar.index');
        Route::post('/hisoblar',           [HisobRejasiController::class, 'store'])->name('hisoblar.store');
        Route::put('/hisoblar/{hisob}',    [HisobRejasiController::class, 'update'])->name('hisoblar.update');
        Route::delete('/hisoblar/{hisob}', [HisobRejasiController::class, 'destroy'])->name('hisoblar.destroy');
        Route::get('/tulov-turlari',           [YangiTulovTuriController::class, 'index'])->name('tulov_turlari.index');
        Route::post('/tulov-turlari',          [YangiTulovTuriController::class, 'store'])->name('tulov_turlari.store');
        Route::put('/tulov-turlari/{tur}',     [YangiTulovTuriController::class, 'update'])->name('tulov_turlari.update');
        Route::delete('/tulov-turlari/{tur}',  [YangiTulovTuriController::class, 'destroy'])->name('tulov_turlari.destroy');
    });


    // ─── Qurilmalar Nazorati ─────────────────────────────────────────

    Route::prefix('qurilmalar')->name('qurilmalar.')->group(function () {
        Route::get('/',                          [QurilmaController::class, 'index'])->name('index');
        Route::get('/yangi',                     [QurilmaController::class, 'create'])->name('create')
            ->middleware('rol.check:admin,menejer,omborchi');
        Route::post('/',                         [QurilmaController::class, 'store'])->name('store')
            ->middleware('rol.check:admin,menejer,omborchi');
        Route::get('/{qurilma}',                 [QurilmaController::class, 'show'])->name('show');
        Route::get('/{qurilma}/tahrirlash',       [QurilmaController::class, 'edit'])->name('edit')
            ->middleware('rol.check:admin,menejer,omborchi');
        Route::put('/{qurilma}',                  [QurilmaController::class, 'update'])->name('update')
            ->middleware('rol.check:admin,menejer,omborchi');
        Route::delete('/{qurilma}',               [QurilmaController::class, 'destroy'])->name('destroy')
            ->middleware('rol.check:admin');
        Route::get('/{qurilma}/loglar',           [QurilmaController::class, 'logs'])->name('loglar');
        Route::post('/{qurilma}/biriktir',         [QurilmaController::class, 'attach'])->name('attach')
            ->middleware('rol.check:admin,menejer');
        Route::post('/{qurilma}/bloklash',         [QurilmaController::class, 'lock'])->name('lock')
            ->middleware('rol.check:admin');
        Route::post('/{qurilma}/ochish',           [QurilmaController::class, 'unlock'])->name('unlock')
            ->middleware('rol.check:admin');
        Route::post('/{qurilma}/ogohlantirish',    [QurilmaController::class, 'warn'])->name('warn')
            ->middleware('rol.check:admin,menejer');
        Route::post('/{qurilma}/ozod-qilish',      [QurilmaController::class, 'release'])->name('release')
            ->middleware('rol.check:admin');
    });

    Route::prefix('qurilma-provayderlar')->name('qurilma-provayderlar.')->middleware('rol.check:admin')->group(function () {
        Route::get('/',                                       [QurilmaProvayderController::class, 'index'])->name('index');
        Route::post('/{provayder}/toggle',                    [QurilmaProvayderController::class, 'toggle'])->name('toggle');
        Route::post('/{provayder}/toggle-mock',               [QurilmaProvayderController::class, 'toggleMock'])->name('toggle-mock');
        Route::get('/{provayder}/sozlamalar',                  [QurilmaProvayderController::class, 'sozlamalar'])->name('sozlamalar');
        Route::post('/{provayder}/sozlamalar',                 [QurilmaProvayderController::class, 'sozlamalarSaqlash'])->name('sozlama-saqlash');
        Route::post('/{provayder}/sozlama-qoshish',            [QurilmaProvayderController::class, 'sozlamaQoshish'])->name('sozlama-qoshish');
        Route::delete('/{provayder}/sozlamalar/{sozlama}',     [QurilmaProvayderController::class, 'sozlamaOchirish'])->name('sozlama-ochirish');
    });


    // ─── Ma'lumotnomalar (Spravochniklar) ──────────────────────────

    Route::prefix('malumotnamalar')->name('malumotnamalar.')->middleware('rol.check:admin,menejer,hisobchi')->group(function () {

        Route::get('/', [MalumotnomalarController::class, 'index'])->name('index');

        // Filiallar
        Route::prefix('filiallar')->name('filiallar.')->middleware('rol.check:admin')->group(function () {
            Route::get('/',              [FilialController::class, 'index'])->name('index');
            Route::post('/',             [FilialController::class, 'store'])->name('store');
            Route::put('/{filial}',      [FilialController::class, 'update'])->name('update');
            Route::delete('/{filial}',   [FilialController::class, 'destroy'])->name('destroy');
        });

        // Kassalar
        Route::prefix('kassalar')->name('kassalar.')->middleware('rol.check:admin')->group(function () {
            Route::get('/',             [KassaController::class, 'index'])->name('index');
            Route::post('/',            [KassaController::class, 'store'])->name('store');
            Route::put('/{kassa}',      [KassaController::class, 'update'])->name('update');
            Route::delete('/{kassa}',   [KassaController::class, 'destroy'])->name('destroy');
        });

        // Birliklar
        Route::prefix('birliklar')->name('birliklar.')->group(function () {
            Route::get('/',             [BirlikController::class, 'index'])->name('index');
            Route::post('/',            [BirlikController::class, 'store'])->name('store')
                ->middleware('rol.check:admin,menejer');
            Route::put('/{birlik}',     [BirlikController::class, 'update'])->name('update')
                ->middleware('rol.check:admin,menejer');
            Route::delete('/{birlik}',  [BirlikController::class, 'destroy'])->name('destroy')
                ->middleware('rol.check:admin');
        });

        // Harajat turlari
        Route::prefix('harajat-turlari')->name('harajat-turlari.')->middleware('rol.check:admin,menejer,hisobchi')->group(function () {
            Route::get('/',                     [HarajatTuriController::class, 'index'])->name('index');
            Route::post('/',                    [HarajatTuriController::class, 'store'])->name('store')
                ->middleware('rol.check:admin,menejer');
            Route::put('/{harajatTuri}',        [HarajatTuriController::class, 'update'])->name('update')
                ->middleware('rol.check:admin,menejer');
            Route::delete('/{harajatTuri}',     [HarajatTuriController::class, 'destroy'])->name('destroy')
                ->middleware('rol.check:admin');
            Route::post('/bog-lash',            [HarajatTuriController::class, 'bogLash'])->name('boglash')
                ->middleware('rol.check:admin,menejer');
            Route::get('/taminotchi-migratsiya', [HarajatTuriController::class, 'taminotchiMigratsiya'])->name('taminotchi-migratsiya')
                ->middleware('rol.check:admin,menejer');
            Route::post('/taminotchi-migratsiya', [HarajatTuriController::class, 'taminotchiMigratsiyaTasdiq'])->name('taminotchi-migratsiya.tasdiq')
                ->middleware('rol.check:admin,menejer');
            Route::post('/manfiy-daromad', [HarajatTuriController::class, 'manfiyDaromadQilish'])->name('manfiy-daromad')
                ->middleware('rol.check:admin,menejer');
        });

        // Pul oqimi kategoriyalari
        Route::prefix('pul-kategoriyalar')->name('pul-kategoriyalar.')->group(function () {
            Route::get('/',                      [PulKategoriyaController::class, 'index'])->name('index');
            Route::post('/',                     [PulKategoriyaController::class, 'store'])->name('store')
                ->middleware('rol.check:admin,menejer');
            Route::put('/{pulKategoriya}',       [PulKategoriyaController::class, 'update'])->name('update')
                ->middleware('rol.check:admin,menejer');
            Route::delete('/{pulKategoriya}',    [PulKategoriyaController::class, 'destroy'])->name('destroy')
                ->middleware('rol.check:admin');
        });

        // Brendlar
        Route::prefix('brendlar')->name('brendlar.')->middleware('rol.check:admin,menejer')->group(function () {
            Route::get('/',            [BrendController::class, 'index'])->name('index');
            Route::post('/',           [BrendController::class, 'store'])->name('store');
            Route::put('/{brend}',     [BrendController::class, 'update'])->name('update');
            Route::delete('/{brend}',  [BrendController::class, 'destroy'])->name('destroy')
                ->middleware('rol.check:admin');
        });

        // Valyutalar
        Route::prefix('valyutalar')->name('valyutalar.')->middleware('rol.check:admin,hisobchi')->group(function () {
            Route::get('/',              [ValyutaController::class, 'index'])->name('index');
            Route::post('/',             [ValyutaController::class, 'store'])->name('store')
                ->middleware('rol.check:admin');
            Route::put('/{valyuta}',     [ValyutaController::class, 'update'])->name('update')
                ->middleware('rol.check:admin');
            Route::delete('/{valyuta}',  [ValyutaController::class, 'destroy'])->name('destroy')
                ->middleware('rol.check:admin');
            Route::post('/cbu-update',     [ValyutaController::class, 'cbuUpdate'])->name('cbu-update')
                ->middleware('rol.check:admin');
        });

        // Tashkilot rekvizitlari
        Route::prefix('tashkilot-rekvizit')->name('tashkilot-rekvizit.')->middleware('rol.check:admin')->group(function () {
            Route::get('/',                             [TashkilotRekvizitController::class, 'index'])->name('index');
            Route::get('/create',                       [TashkilotRekvizitController::class, 'create'])->name('create');
            Route::post('/',                            [TashkilotRekvizitController::class, 'store'])->name('store');
            Route::get('/{tashkilotRekvizit}/edit',     [TashkilotRekvizitController::class, 'edit'])->name('edit');
            Route::put('/{tashkilotRekvizit}',          [TashkilotRekvizitController::class, 'update'])->name('update');
            Route::delete('/{tashkilotRekvizit}',       [TashkilotRekvizitController::class, 'destroy'])->name('destroy');
        });

        // Shartnoma rekvizitlari
        Route::prefix('shartnoma-rekvizit')->name('shartnoma-rekvizit.')->middleware('rol.check:admin')->group(function () {
            Route::get('/',                              [ShartnomRekvizitController::class, 'index'])->name('index');
            Route::post('/',                             [ShartnomRekvizitController::class, 'store'])->name('store');
            Route::put('/{shartnomRekvizit}',            [ShartnomRekvizitController::class, 'update'])->name('update');
            Route::delete('/{shartnomRekvizit}',         [ShartnomRekvizitController::class, 'destroy'])->name('destroy');
        });

        // Statuslar va sabablar
        Route::prefix('statuslar')->name('statuslar.')->group(function () {
            Route::get('/',                  [StatusSababController::class, 'index'])->name('index');
            Route::post('/',                 [StatusSababController::class, 'store'])->name('store')
                ->middleware('rol.check:admin');
            Route::put('/{statusSabab}',     [StatusSababController::class, 'update'])->name('update')
                ->middleware('rol.check:admin');
            Route::delete('/{statusSabab}',  [StatusSababController::class, 'destroy'])->name('destroy')
                ->middleware('rol.check:admin');
        });

        // Viloyatlar va Tumanlar
        Route::prefix('viloyatlar')->name('viloyatlar.')->group(function () {
            Route::get('/',                         [ViloyatController::class, 'index'])->name('index');
            Route::get('/api',                      [ViloyatController::class, 'apiRoyhati'])->name('api');
            Route::get('/api/tumanlar',             [ViloyatController::class, 'apiBarcha'])->name('api.tumanlar');
            Route::get('/{viloyat}/tumanlar',       [ViloyatController::class, 'apiTumanlar'])->name('tumanlar');
            Route::middleware('rol.check:admin')->group(function () {
                Route::post('/nom-yangilash', [ViloyatController::class, 'nomlarYangilash'])->name('nom-yangilash');
                Route::put('/{viloyat}',            [ViloyatController::class, 'updateViloyat'])->name('update');
                Route::post('/tuman',               [ViloyatController::class, 'storeTuman'])->name('tuman.store');
                Route::put('/tuman/{tuman}',        [ViloyatController::class, 'updateTuman'])->name('tuman.update');
            });
        });

        // Pochta Shablonlari
        Route::prefix('pochta-shablonlar')->name('pochta-shablonlar.')->middleware('rol.check:admin')->group(function () {
            Route::get('/',                         [PochtaShablonController::class, 'index'])->name('index');
            Route::post('/',                        [PochtaShablonController::class, 'store'])->name('store');
            Route::put('/{pochtaShablon}',          [PochtaShablonController::class, 'update'])->name('update');
            Route::delete('/{pochtaShablon}',       [PochtaShablonController::class, 'destroy'])->name('destroy');
        });



    });


});