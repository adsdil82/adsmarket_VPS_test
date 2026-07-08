# ADSmarket POS moduli — HANDOFF SUMMARY

> Server: `/var/www/adsmarket` (SSH: `nasiyapro`, config `/d/ClaudeProjekt/.ssh/config`)
> Local nusxa: `D:\ClaudeProjekt\adsmarket_sql\`
> Git: server repoda mavjud. Bosqich 1-4(qism1) — commit `bcef5d2`. Bosqich 4
> PIN-terminal UI-test tuzatishlari — commit `97bf290`. Multi-barkod — commit `d05b53c`.
> Sana: 2026-07-08 (birinchi yaratilgan: 2026-07-07)

## 1. Umumiy kontekst

Foydalanuvchi (Uzbek/Cyrillic, 28 bo'limli spec) to'liq alohida **POS (Kassa) moduli**
buyurtma qildi: unified POS menyu guruhi, Dashboard, Kassir smenasi, Qaytim/Vozvrat,
Fullscreen PIN-kirish terminal, multi-barkod, chek/printer sozlamalari, to'liq RBAC va
audit-log. Ish **bosqichma-bosqich** rejim bilan olib borildi — foydalanuvchi har
bosqichdan keyin keyingisiga aniq buyruq berdi:

- ✅ **Bosqich 1** — POS guruh (sidebar), POS Dashboard, POS hisobotlar
- ✅ **Bosqich 2** — Kassir smenasi (ochish/yopish/topshirish)
- ✅ **Bosqich 3** — Qaytim/Vozvrat moduli
- ✅ **Bosqich 4 (qism 1)** — Fullscreen PIN-kirish rejimi — backend TUGALLANDI,
  **real brauzerda ham to'liq test qilindi (keyingi sessiyada), 2 ta xato topilib tuzatildi**
- ✅ **Bosqich 4 (qism 2)** — Multi-barkod — **TUGALLANDI va TEST QILINDI**
- ⬜ **Bosqich 4 (qolgan qismi)** — chek/printer sozlamalari, POS umumiy sozlamalar
  sahifasi — **BOSHLANMAGAN**

**Muhim tamoyil butun sessiya davomida**: *"Mavjud ishlayotgan funksiyalarni buzma."*
Shu sababli ko'p joyda **refactor emas, duplikatsiya** yo'li tanlandi (masalan,
`terminal/sotish.blade.php` — `ombor/pos/index.blade.php`ning mustaqil nusxasi).

---

## 2. Bosqich 1 — POS guruh, Dashboard, Hisobotlar (TUGALLANGAN)

- Sidebar'da yagona **"POS"** guruhi yaratildi (`layouts/app.blade.php`), eski
  "Kassa POS" / "Sotuv tarixi" / "Kassalar" / "Kassa transferi" shu guruhga ko'chirildi
  (marshrutlar/ruxsatlar buzilmadi).
- Yangi **POS Dashboard** (`/pos/dashboard`, `PosController::dashboard()`,
  `ombor/pos/dashboard.blade.php`) — real ma'lumot bilan kartalar+jadvallar.
- Yangi **POS hisobotlar** (`/pos/hisobotlar`, `PosController::hisobotlar()`,
  `ombor/pos/hisobotlar.blade.php`) — filtrlar + Excel export.
- **Tuzatilgan xato**: `dashboard()` dagi JOIN'da noaniq `holat` ustuni
  (`pos_sotuv.holat` bilan `foydalanuvchilar.holat` orasida ziddiyat) — qator
  qo'shib aniqlashtirildi.

## 3. Bosqich 2 — Kassir smenasi (TUGALLANGAN)

**Yangi jadval**: `057_pos_smenalar.php`
- `pos_smenalar`: smena_raqami (unique, format `SM-{filial}-{Ymd}-{seq}`), filial_id,
  xodim_id, ochilgan_vaqt, yopilgan_vaqt, dastlabki_qoldiq, hisoblangan_qoldiq,
  yakuniy_qoldiq, farq, topshirilgan_summa, topshirish_holati
  (`yoq/kutilmoqda/tasdiqlangan/rad_etildi`), qabul_qilgan_id, qabul_vaqti,
  rad_sababi, holat (`ochiq/yopiq`).
- `pos_sotuv` jadvaliga `smena_id` (nullable FK) qo'shildi.

**Model**: `app/Models/PosSmena.php`
- `joriyNaqdQoldiq()` — joriy naqd qoldiqni hisoblaydi:
  ```php
  dastlabki_qoldiq + SUM(naqd_summa - qayta_pul) [tugallangan sotuvlar]
                    - SUM(qaytimlar naqd) [tugallangan qaytimlar]
  ```
- `scopeOchiq()`, `static yangiSmenaRaqami($filialId)`.

**Controller**: `app/Http/Controllers/PosSmenaController.php`
- `static joriy(int $filialId): ?PosSmena` — **markaziy metod**, boshqa
  controller'lar (`PosController`, `PosTerminalController`) shundan foydalanadi.
- `ochishForma/ochish/yopishForma/yopish/topshirish/topshirishTasdiqlash/
  topshirishRad/royxat/korish`.
- `egalikTekshir()` — faqat egasi yoki admin/menejer amal qila oladi (403).

**Muhim biznes-logika**: `PosController::index()` va `store()` endi **majburiy**
ochiq smena talab qiladi — smena bo'lmasa `pos.smena.ochish-forma`ga
redirect (yoki `store()`da 422 xato).

**Yangi view'lar**: `resources/views/ombor/pos/smena/{ochish,yopish,royxat,korish}.blade.php`

**Sidebar/route**: `pos.smena.*` prefiksi, "Kassir smenalari" nav-item.

## 4. Bosqich 3 — Qaytim/Vozvrat (TUGALLANGAN)

**Yangi jadvallar**:
- `058_pos_qaytim_kategoriya.php` — `pul_kategoriyalar`ga `CF-2740` ("POS savdo
  qaytimi", yunalish=chiqim, ota=`CF-2700`) qo'shadi.
- `059_pos_qaytimlar.php` — `pos_qaytimlar` (qaytim_raqami unique
  `QR-{filial}-{Ymd}-{seq}`, sotuv_id, smena_id — **qaytarish vaqtidagi** smena,
  tolov_turi, jami_summa, sabab, holat) + `pos_qaytim_tafsilot`.

**Model**: `PosQaytim.php`, `PosQaytimTafsilot.php`.
`PosTafsilot::qaytarilganMiqdor()` — shu tafsilot bo'yicha jami qaytarilgan
miqdorni hisoblaydi (faqat `holat=tugallangan` qaytimlar bo'yicha) — **ortiqcha
qaytarishni oldini olish** shu metodga tayanadi.

**Controller**: `app/Http/Controllers/PosQaytimController.php`
- `boshlash/saqlash/royxat/korish`.
- `saqlash()`: DB::transaction ichida yozuv + `StockService::kirim()` (ombor
  qoldig'ini tiklaydi), keyin **transaction TASHQARISIDA**
  `TulovService::pulOqimigaYozKassaTuri(..., kategoriyaKodi:'CF-2740',
  manbaTur:'pos_qaytim', yunalish:'chiqim')` — pul oqimidan **chiqim** yoziladi.
- Har qatorda live tekshiruv: `qolgan = sotilgan - qaytarilgan` — 0 bo'lsa
  ro'yxatdan chiqariladi, ortiqcha kiritilsa 422 xato.

**Yangi view'lar**: `resources/views/ombor/pos/qaytim/{boshlash,royxat,korish}.blade.php`
`ombor/pos/tarix.blade.php` va `chek.blade.php`ga "Qaytim" tugmasi qo'shildi
(faqat `holat===tugallangan` bo'lganda).

**To'liq end-to-end test qilingan**: smena ochish → sotuv (−2 dona) → qaytim
(+1 dona) → ombor qoldig'i tiklanishi, smena naqd balansi, `pul_oqimlari`
`yunalish=chiqim` yozuvi, ortiqcha qaytarish bloklanishi — barchasi tasdiqlangan,
test ma'lumotlari tozalangan.

## 5. Bosqich 4 (qism 1) — Fullscreen PIN-kirish rejimi (TUGALLANGAN)

### 5.1 Yangi migratsiyalar
- **`060_foydalanuvchi_pin.php`** — `foydalanuvchilar`ga qo'shadi:
  `pin_kod` (nullable string), `pin_bloklangan_gacha` (nullable timestamp),
  `pin_xato_soni` (unsignedTinyInteger, default 0).
- **`061_pos_terminal_loglar.php`** — `pos_terminal_loglar` jadvali: xodim_id,
  filial_id, `hodisa` enum(`kirish/xato_pin/bloklandi/qulflash/yechish/chiqish`),
  ip, izoh, timestamps.

### 5.2 Model o'zgarishlari
**`app/Models/Foydalanuvchi.php`**:
```php
protected $fillable = [..., 'pin_kod', 'pin_xato_soni', 'pin_bloklangan_gacha', ...];
protected $hidden   = [..., 'pin_kod', ...];
protected $casts    = [..., 'pin_kod' => 'hashed', 'pin_bloklangan_gacha' => 'datetime'];

public function pinTogri(string $pin): bool { ... Hash::check ... }
public function pinBloklanganmi(): bool { ... isFuture() ... }
```
> ⚠️ **MUHIM TUZATILGAN XATO**: dastlab `pin_xato_soni` va `pin_bloklangan_gacha`
> `$fillable`da YO'Q edi → `update()` chaqiruvlari bu maydonlarni **jimgina
> e'tiborsiz qoldirar edi** (mass-assignment himoyasi). Tinker orqali test qilinmaguncha
> aniqlanmagan. Hozir tuzatilgan va deploy qilingan.

**`app/Models/PosTerminalLog.php`** — yangi model, `static yoz($hodisa, $xodimId, $filialId, $izoh=null)`.

### 5.3 Controller
**`app/Http/Controllers/PosTerminalController.php`** — yangi:
- `pinForma()` — kassir tanlash + PIN klaviatura ekrani.
- `pinTekshir(Request)` — dastlabki kirish, xato hisoblash, bloklash (5 xato →
  15 daqiqa blok), `session(['pos_terminal' => [...]])`, `PosTerminalLog::yoz('kirish', ...)`.
- `index()` — fullscreen savdo ekrani; sessiyani tekshiradi, ochiq smenani talab
  qiladi (`PosSmenaController::joriy()`).
- `qulflash()` — ekranni JS orqali qulflaydi (`session('pos_terminal.qulflangan')=true`).
- `yechish(Request)` — PIN qayta kiritilganda: **barcha nomzod kassirlar bo'yicha
  sikl** (`pinTogri()` har biriga), chunki hash to'g'ridan-to'g'ri qidirilmaydi.
  Boshqa kassir aniqlansa `boshqa_kassir:true` qaytaradi — frontend savatni
  tozalab, sahifani qayta yuklaydi.
- `chiqish()` — sessiyani tozalaydi, `pos.terminal.pin-forma`ga redirect.

> ⚠️ **MUHIM TUZATILGAN XATO #2**: `pinForma()` va `yechish()`dagi nomzodlar
> so'rovi dastlab `->where('filial_id', $filialId)` bilan filtrlangan edi.
> **Admin (`rol=admin`) foydalanuvchilarning `filial_id`si odatda `NULL`** —
> shu sabab admin PIN bilan hech qachon topilmas edi ("PIN noto'g'ri" xatosi,
> hatto to'g'ri PIN kiritilsa ham). Tuzatildi:
> `->where(fn($q) => $q->where('filial_id', $filialId)->orWhere('rol', 'admin'))`

### 5.4 Marshrutlar (`routes/web.php`)
```php
Route::prefix('terminal')->name('terminal.')->group(function () {
    Route::get('/pin',       [PosTerminalController::class, 'pinForma'])->name('pin-forma');
    Route::post('/pin',      [PosTerminalController::class, 'pinTekshir'])->name('pin-tekshir');
    Route::get('/',          [PosTerminalController::class, 'index'])->name('index');
    Route::post('/qulflash', [PosTerminalController::class, 'qulflash'])->name('qulflash');
    Route::post('/yechish',  [PosTerminalController::class, 'yechish'])->name('yechish');
    Route::get('/chiqish',   [PosTerminalController::class, 'chiqish'])->name('chiqish');
});
```
(shu `Route::middleware('auth')` blok ichida, `pos.*` guruhidan keyin joylashgan.)

`admin.foydalanuvchilar.pin` — `POST /admin/foydalanuvchilar/{foydalanuvchi}/pin`
→ `AdminController::foydalanuvchiPinOrnat()` (parol-reset patterniga o'xshash,
`digits_between:4,6|confirmed` validatsiya bilan).

### 5.5 View'lar (yangi)
- **`resources/views/terminal/pin.blade.php`** — mustaqil (o'z `<html>`,
  Bootstrap 5.3.3 CDN), kassir tanlash select + raqamli klaviatura.
- **`resources/views/terminal/sotish.blade.php`** — mustaqil fullscreen savdo
  ekrani. **`ombor/pos/index.blade.php`dan qasddan duplikatsiya qilingan**
  (refactor emas!) — bir xil `/pos/tovarlar` va `/pos/saqlash` endpoint'laridan
  foydalanadi, ustiga: qulflash overlay (PIN qayta kiritish), avtomatik
  qulflash timer (10 daqiqa, `autoLockDaqiqa` orqali sozlanadigan).

### 5.6 Admin UI
`resources/views/admin/foydalanuvchilar.blade.php` — har bir kassir/menejer/admin
qatoriga PIN o'rnatish tugmasi (🔢 ikonka) + modal qo'shildi (parol-reset
modaliga o'xshash pattern).

### 5.7 Sidebar
`layouts/app.blade.php`:
- "Fullscreen kassa" nav-item (`target="_blank"`, `route('terminal.pin-forma')`)
  — "Kassa POS" ostiga qo'shildi.
- `$aktiv_grup` aniqlash qatoriga `terminal.*` qo'shildi.

### 5.8 Xavfsizlik chegarasi (ONGLI QAROR — foydalanuvchiga eslatish kerak)
PIN qatlami **faqat "qaysi kassir terminaldan foydalanmoqda"ni aniqlaydi**
(smena/audit maqsadida). Haqiqiy xavfsizlik chegarasi — odatiy Laravel
`auth` sessiyasi (qurilma allaqachon tizimga kirgan bo'lishi kerak).
`/pos/tovarlar` va `/pos/saqlash` endpoint'lari **PIN bo'yicha qayta
tekshirilmaydi** — ular `Auth::user()->filial_id`ga tayanadi, PIN-sessiyaga
emas. Bu **qasddan qilingan tanlov** (mavjud, test qilingan POS asosini
buzmaslik uchun), lekin **haqiqiy production xavfsizlik talabi** bo'lsa,
kelajakda qayta ko'rib chiqish kerak.

### 5.9 Real brauzerda test qilingandan keyin topilgan va tuzatilgan 2 ta xato (commit `97bf290`)

Avvalgi sessiyada bu funksiya faqat **tinker/curl** darajasida tekshirilgan edi
(section 7 pastda). Keyingi sessiyada Chrome orqali to'liq PIN-kirish →
lock/unlock → kassir-almashtirish oqimi bosqichma-bosqich sinovdan o'tkazildi
va shu jarayonda ikkita jiddiy xato topildi:

1. **`sotuvchi` roli PIN tizimidan butunlay chetlab qo'yilgan edi.** Tizimda
   ikkita alohida rol bor — `kassir` ("Kassir", hech kim ishlatmaydi) va
   `sotuvchi` ("Sotuvchi", **haqiqiy POS operatorlari shu rolda**). Uchta joyda
   ro'yxat qat'iy `['admin','menejer','kassir']` bilan cheklangan edi:
   - `resources/views/admin/foydalanuvchilar.blade.php` (86-qator) — "POS PIN
     kod o'rnatish" tugmasi `sotuvchi` qatorlarida umuman ko'rinmas edi.
   - `app/Http/Controllers/PosTerminalController.php` (2 joy — `pinForma()` va
     `yechish()` dagi nomzodlar so'rovi) — haqiqiy kassirlar terminal
     nomzodlar ro'yxatida topilmas edi.
   - `app/Http/Controllers/PosController.php` (`hisobotlar()` dagi "kassirlar"
     filtr-dropdown) — POS hisobotlarda ham `sotuvchi` ko'rinmas edi.
   **Tuzatish**: har uch joyga `'sotuvchi'` qo'shildi (additiv, `kassir`ga
   tegilmadi).
2. **Terminal sarlavhasi/qulflash ekrani noto'g'ri odamni ko'rsatardi.**
   `resources/views/terminal/sotish.blade.php` (89- va 229-qatorlar, JS
   `JORIY_XODIM_ID`) `$smena->xodim->ism_familiya` (ya'ni **smenani ochgan
   xodim**) dan foydalangan, PIN orqali joriy kirgan kassirdan emas. Agar
   smenani boshqa odam ochib, keyin PIN orqali boshqa kassir terminaldan
   foydalansa — sarlavha noto'g'ri ism ko'rsatar edi (audit maqsadiga zid).
   **Tuzatish**: `PosTerminalController::index()` endi
   `$kassir = Foydalanuvchi::find($xodimId)` ni view'ga uzatadi, view esa
   `$kassir->ism_familiya`dan foydalanadi.

Test qilingan (brauzerda, real UI orqali): PIN o'rnatish (admin panel),
kassir tanlash + PIN klaviatura (noto'g'ri/to'g'ri), terminal ochilishi va
sarlavhadagi kassir nomi to'g'riligi, qulflash/yechish (noto'g'ri/to'g'ri PIN),
boshqa kassir PIN bilan yechilganda avtomatik almashish + savat tozalanishi +
`location.reload()`, audit-log (`pos_terminal_loglar`) barcha hodisalarni
to'g'ri yozgani. Test PIN'lar va audit-log yozuvlari tozalangan; "Tizim Admin"
hisobidagi oldindan mavjud PIN test paytida **4321**ga almashtirildi (asl
qiymati hash bo'lgani uchun tiklab bo'lmadi).

---

## 6. Bosqich 4 (qism 2) — Multi-barkod (TUGALLANGAN, commit `d05b53c`)

Bitta tovar uchun bir nechta shtrix-kod. Asosiy `tovar_katalog.barkod` ustuni
**o'zgarishsiz qoldi** (orqaga moslik), yangi jadval faqat qo'shimcha
barkodlarni saqlaydi.

**Yangi**:
- `database/migrations/062_tovar_barkodlar.php` — `tovar_barkodlar` (id,
  tovar_id FK → `tovar_katalog` cascade-delete, barkod unique, timestamps).
- `app/Models/TovarBarkod.php` — model, `belongsTo(TovarKatalog::class)`.
- `TovarKatalog::barkodlar()` — yangi `hasMany` relation.

**O'zgartirilgan**:
- `PosController::tovarlar()` — qidiruv endi `orWhereHas('barkodlar', ...)`
  orqali qo'shimcha barkodlarni ham qamrab oladi; har bir natijaga
  `barkodlar_royxati` (asosiy + qo'shimcha, birlashtirilgan) massivi qo'shildi.
- `resources/views/ombor/pos/index.blade.php` va
  `resources/views/terminal/sotish.blade.php` (**ikkalasi ham** — qasddan
  duplikatsiya, TODO_NEXT'da ogohlantirilganidek) — `barkodSkan()` JS'idagi
  `data.find(t => t.barkod === q)` endi
  `data.find(t => (t.barkodlar_royxati || [t.barkod]).includes(q))`ga
  almashtirildi.
- `app/Http/Controllers/TovarKatalogController.php` (`store()`/`update()`) —
  `qoshimcha_barkodlar[]` massivini validatsiya qilib saqlaydi/yangilaydi
  (`update()`da eskilarini o'chirib qayta yozadi — forma har doim to'liq
  ro'yxatni qayta yuboradi).
- `resources/views/ombor/katalog/create.blade.php` va `edit.blade.php` —
  "Qo'shimcha shtrix-kodlar" bo'limi, JS orqali dinamik qo'shish/o'chirish
  (`qoshimchaBarkodQosh()`, har bir qatorda o'chirish tugmasi).

**Test qilingan (brauzerda)**: mahsulotga (`LG Vc 76 a`, id=135) qo'shimcha
barkod qo'shildi va saqlandi → oddiy POS ekranida shu barkod bilan
skanerlanganda mahsulot to'g'ri savatga tushdi → xuddi shu barkod fullscreen
terminalda ham ishladi → **regressiya yo'q**: asosiy (eski) barkod ikkala
ekranda ham muammosiz davom etmoqda. Test barkodi bazadan tozalangan.

---

## 7. O'zgargan/yaratilgan fayllar ro'yxati

> Bosqich 1-4(qism1) — commit `bcef5d2`da. PIN-terminal UI-test tuzatishlari —
> commit `97bf290`da. Multi-barkod — commit `d05b53c`da. Quyidagi ro'yxat
> **tarixiy** (bu fayllar avval untracked/modified holatda edi, hozir hammasi
> commit qilingan).

### Yangi (endi commit qilingan) — bevosita POS bilan bog'liq:
```
app/Http/Controllers/PosQaytimController.php
app/Http/Controllers/PosSmenaController.php
app/Http/Controllers/PosTerminalController.php
app/Models/PosQaytim.php
app/Models/PosQaytimTafsilot.php
app/Models/PosSmena.php
app/Models/PosTerminalLog.php
database/migrations/057_pos_smenalar.php
database/migrations/058_pos_qaytim_kategoriya.php
database/migrations/059_pos_qaytimlar.php
database/migrations/060_foydalanuvchi_pin.php
database/migrations/061_pos_terminal_loglar.php
resources/views/ombor/pos/dashboard.blade.php
resources/views/ombor/pos/hisobotlar.blade.php
resources/views/ombor/pos/qaytim/         (papka)
resources/views/ombor/pos/smena/          (papka)
resources/views/terminal/                 (papka: pin.blade.php, sotish.blade.php)
app/Models/TovarBarkod.php
database/migrations/062_tovar_barkodlar.php
```

### O'zgartirilgan (modified) — bevosita POS bilan bog'liq:
```
app/Http/Controllers/AdminController.php     (+ foydalanuvchiPinOrnat)
app/Http/Controllers/PosController.php       (+ smena majburiyligi, dashboard/hisobotlar)
app/Models/Foydalanuvchi.php                 (+ PIN maydonlari/metodlar)
app/Models/PosSotuv.php                      (+ smena_id)
app/Models/PosTafsilot.php                   (+ qaytarilganMiqdor)
resources/views/admin/foydalanuvchilar.blade.php  (+ PIN modal)
resources/views/layouts/app.blade.php        (+ POS guruh, Fullscreen kassa link)
resources/views/ombor/pos/chek.blade.php     (+ Qaytim tugmasi)
resources/views/ombor/pos/index.blade.php    (+ smena-bar, + multi-barkod skanerlash)
resources/views/ombor/pos/tarix.blade.php    (+ Qaytim tugmasi)
routes/web.php                               (+ pos.smena.*, pos.qaytim.*, terminal.*, admin PIN route)
app/Models/TovarKatalog.php                  (+ barkodlar() relation)
app/Http/Controllers/TovarKatalogController.php (+ qo'shimcha barkodlar saqlash)
resources/views/ombor/katalog/create.blade.php  (+ qo'shimcha barkodlar UI)
resources/views/ombor/katalog/edit.blade.php    (+ qo'shimcha barkodlar UI)
resources/views/terminal/sotish.blade.php    (+ to'g'ri kassir nomi, + multi-barkod skanerlash)
```

### Boshqa (bu sessiyaga bevosita aloqasi yo'q, oldingi sessiyalardan qolgan
### tugallanmagan/tugallangan ishlar — HANDOFF uchun eslatib o'tiladi, lekin
### POS spec doirasiga kirmaydi):
```
Bonus tovar avtomatik hisoblash (047-049 migratsiyalar, PLReportService,
  BLReportService, hisobot/bonus_tovarlar.blade.php)
Etiketka shablonlari (050, EtiketkaShablon model, BarcodeLabelController)
Hisobot shablonlari (051, HisobotShablon model, hisobot/konstruktor.blade.php)
Balans tenglashtiruvchi/rezerv (052-053, pul-oqimlari/balans/index.blade.php)
Eski-ID unique ustunlar (054-056 — insertOrIgnore dedup uchun)
Kirim/Chiqim hujjatlar refactor (ombor/kirim/, ombor/chiqim/ papkalar,
  hujjatlar/ subpapkalar, ImportService)
lang/uz/validation.php — yangi validatsiya xabarlari
```
> Bu fayllar hali ham commit qilinmagan holatda turibdi (POS ishlaridan
> mustaqil). Agar ular tugallangan bo'lsa, POS commit'laridan **alohida**
> commit qilish tavsiya etiladi.

---

## 8. Test qilingan narsalar

**Bosqich 4 qism 1 (PIN-terminal), birinchi sessiya** — faqat tinker/curl:
1. `Foydalanuvchi::pinTogri()` / `pinBloklanganmi()` — to'g'ri/noto'g'ri PIN,
   hash tekshiruvi.
2. Bloklash logikasi: `pin_xato_soni++` → 5-chida `pin_bloklangan_gacha` o'rnatiladi
   → `pinBloklanganmi()=true`.
3. To'liq terminal oqimi tinker/curl orqali (route darajasida `302`, `500` yo'q).
4. `AdminController::foydalanuvchiPinOrnat()` — PIN o'rnatish, keyin
   `pinTogri()` bilan tasdiqlash.

**Bosqich 4 qism 1, ikkinchi sessiya — real brauzerda** (bo'lim 5.9'da
batafsil): PIN o'rnatish, kassir tanlash + klaviatura, terminal ochilishi +
to'g'ri kassir nomi, qulflash/yechish, boshqa-kassir almashish, audit-log —
barchasi Chrome orqali tasdiqlangan, 2 ta xato tuzatilgan.

**Bosqich 4 qism 2 (multi-barkod), real brauzerda** (bo'lim 6'da batafsil):
qo'shimcha barkod qo'shish, ikkala ekranda (oddiy POS + fullscreen terminal)
skanerlash, asosiy barkod regressiyasiz ishlashi.

**Barcha test ma'lumotlari** (PIN qiymatlari, `pos_terminal_loglar` yozuvlari,
test barkodlar) har safar tozalangan — production ma'lumotlarga ta'sir
qilmagan.

---

## 9. Keyingi bosqichlar (foydalanuvchi so'ragan, hali BOSHLANMAGAN)

1. **Chek/printer sozlamalari** — `pos_printer_settings`, `pos_receipt_templates`
   jadvallari, POS sozlamalar sahifasi — hali boshlanmagan.
2. **POS sozlamalari** umumiy sahifasi (`pos_settings` jadvali) — auto-lock
   daqiqasi hozircha kodda hardcoded (`AUTO_LOCK_DAQIQA=10`), sozlamalar
   sahifasi orqali boshqarilishi kerak edi spec bo'yicha.
3. Mobil/planshet ekranida fullscreen terminal ko'rinishi hali maxsus test
   qilinmagan (desktop brauzerda to'liq tasdiqlangan).
