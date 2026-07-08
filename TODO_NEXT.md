# TODO_NEXT.md — POS moduli, keyingi sessiya uchun

> To'liq texnik tafsilot uchun qarang: [HANDOFF_SUMMARY.md](HANDOFF_SUMMARY.md)
> Commitlar: `bcef5d2` (Bosqich 1-4 qism1), `97bf290` (PIN-terminal UI-test
> tuzatishlari), `d05b53c` (multi-barkod).

## 1) Nima bajarildi

- **Bosqich 1** ✅ — POS sidebar guruhi, POS Dashboard (`/pos/dashboard`),
  POS hisobotlar (`/pos/hisobotlar`, Excel export).
- **Bosqich 2** ✅ — Kassir smenasi: ochish/yopish/topshirish/tasdiqlash/rad,
  `PosSmenaController::joriy()` — barcha POS savdolar endi ochiq smena talab qiladi.
- **Bosqich 3** ✅ — Qaytim/Vozvrat: ombor qoldig'ini tiklash, smena naqd
  balansini kamaytirish, `pul_oqimlari`ga chiqim yozish, ortiqcha qaytarishni
  bloklash.
- **Bosqich 4, qism 1** ✅ — Fullscreen PIN-kirish rejimi: kassir tanlash,
  PIN klaviatura, bloklash (5 xato/15 daqiqa), qulflash/yechish, audit-log
  (`pos_terminal_loglar`), admin panelda PIN o'rnatish UI. **Real brauzerda
  to'liq test qilingan**, 2 ta xato topilib tuzatilgan (`sotuvchi` roli PIN
  tizimidan chetlangan edi; terminal sarlavhasi smena-ochuvchini ko'rsatardi,
  joriy PIN-kassirni emas) — tafsilot HANDOFF_SUMMARY.md 5.9-bo'limda.
- **Bosqich 4, qism 2** ✅ — Multi-barkod: `tovar_barkodlar` jadvali, admin
  formada qo'shimcha barkodlar UI, POS va fullscreen terminalda skanerlash
  — real brauzerda test qilingan, tafsilot HANDOFF_SUMMARY.md 6-bo'limda.

## 2) Nima test qilindi

- Barcha backend logika **tinker** orqali funksional test qilindi (PIN
  hashing, bloklash, to'liq terminal oqimi: kirish→qulflash→yechish→chiqish).
- Barcha yangi/o'zgargan marshrutlar **curl** orqali tekshirildi — `302`
  (login redirect), `500` yo'q, `laravel.log`da yangi xato yo'q.
- Qaytim modulida to'liq end-to-end ssenariy: sotuv → qisman qaytim → ombor/
  smena/pul-oqimi muvofiqligi → ortiqcha qaytarish bloklanishi.
- **PIN-terminal to'liq real brauzerda (Chrome) test qilindi**: PIN o'rnatish,
  kassir tanlash+klaviatura, terminal ochilishi, qulflash/yechish, boshqa
  kassir bilan almashish, audit-log yozuvlari.
- **Multi-barkod real brauzerda test qilindi**: qo'shimcha barkod qo'shish,
  ikkala ekranda (oddiy POS + fullscreen terminal) skanerlash, asosiy barkod
  regressiyasiz ishlashi.
- **Barcha test ma'lumotlari tozalangan** (test PIN'lar, test smenalar, test
  qaytimlar, `pos_terminal_loglar` test yozuvlari, test barkodlar) — production'ga
  ta'sir yo'q. Istisno: "Tizim Admin" hisobidagi oldindan mavjud PIN test
  paytida qayta o'rnatilgan (4321) — asl qiymati tiklanmagan (hash edi).

## 3) Nima hali tugallanmagan

- ❌ **Chek/printer sozlamalari** (`pos_printer_settings`,
  `pos_receipt_templates`) — boshlanmagan.
- ❌ **POS sozlamalari sahifasi** (`pos_settings`) — boshlanmagan; hozircha
  auto-lock daqiqasi (`PosTerminalController::AUTO_LOCK_DAQIQA = 10`) kodda
  hardcoded.
- ⚠️ Mobil/planshet ekranida fullscreen terminal ko'rinishi maxsus test
  qilinmagan (faqat desktop brauzerda tasdiqlangan).
- ✅ Server repo — **hammasi commit qilingan** (3 ta commit, yuqorida). POS
  bilan bog'liq bo'lmagan boshqa ishlar (bonus tovar, etiketka shablon,
  balans, kirim/chiqim refactor) hali alohida holatda — pastdagi "Git holati"
  bo'limiga qarang.

## 4) Keyingi sessionda qaysi fayldan boshlash kerak

1. Avval **[HANDOFF_SUMMARY.md](HANDOFF_SUMMARY.md)**ni o'qing — to'liq
   texnik kontekst shu yerda.
2. Agar **chek/printer sozlamalari**dan boshlanadigan bo'lsa:
   - Yangi migratsiya raqami: `063` (oxirgisi `062_tovar_barkodlar.php`).
   - `PosController::chekKorish()` — hozirgi chek ko'rinishini o'rganib chiqing.
3. Agar **POS sozlamalari sahifasi**dan boshlanadigan bo'lsa:
   - `pos_settings` jadvali (yoki mavjud sozlamalar patterniga qarang —
     `admin_sozlamalar` kabi boshqa modullar qanday qilingan tekshiring).
   - `PosTerminalController::AUTO_LOCK_DAQIQA` konstantasini shu sozlamadan
     o'qiydigan qilib almashtirish kerak bo'ladi.

## 5) POS fullscreen PIN login / lock-unlock / multi-barkod / chek-printer — qolgan ishlar

| Bo'lim | Holat | Izoh |
|---|---|---|
| PIN login (kassir tanlash + klaviatura) | ✅ Tugallandi, brauzerda test qilingan | `terminal/pin.blade.php`, `PosTerminalController::pinForma/pinTekshir` |
| Bloklash (5 xato/15 daqiqa) | ✅ Tugallandi | `Foydalanuvchi::pinBloklanganmi()` |
| Lock/Unlock (qulflash/yechish) | ✅ Tugallandi, brauzerda test qilingan | `PosTerminalController::qulflash/yechish` |
| Avtomatik qulflash (inaktivlik timeri) | ✅ Kod yozildi, ⚠️ real vaqt sinovi yo'q | JS: `terminal/sotish.blade.php` → `faollikKuzatish()`, hozircha 10 daqiqa hardcoded |
| Kassir almashtirish (boshqa PIN bilan unlock) | ✅ Tugallandi, brauzerda test qilingan | `yechish()` → `boshqa_kassir:true` → JS `location.reload()` |
| Audit-log (`pos_terminal_loglar`) | ✅ Tugallandi | `kirish/xato_pin/bloklandi/qulflash/yechish/chiqish` hodisalari |
| Multi-barkod | ✅ Tugallandi, brauzerda test qilingan | `tovar_barkodlar` jadvali, `TovarKatalog::barkodlar()` |
| Chek shabloni sozlamalari | ❌ Boshlanmagan | `pos_receipt_templates` |
| Printer sozlamalari | ❌ Boshlanmagan | `pos_printer_settings` |
| POS umumiy sozlamalar sahifasi | ❌ Boshlanmagan | `pos_settings`, auto-lock daqiqasini shu yerdan boshqarish kerak |

## 6) Ehtiyot bo'lish kerak bo'lgan joylar

- **`terminal/sotish.blade.php` va `ombor/pos/index.blade.php` — QASDDAN
  duplikatsiya qilingan** (refaktor emas). Savat/to'lov logikasi **VA** endi
  barkod-skan logikasi ham ikkalasida bir xil bo'lishi kerak — multi-barkod
  qo'shilganda ikkalasi ham yangilangan, kelajakda ham shunday davom eting.
- **`Foydalanuvchi::$fillable`** — PIN bilan bog'liq maydonlarni (`pin_kod`,
  `pin_xato_soni`, `pin_bloklangan_gacha`) olib tashlamang — avvalgi
  sessiyada ularning yo'qligi sabab jiddiy "jim" xato bo'lgan edi.
- **Rol nomlari — `sotuvchi` vs `kassir`**: tizimda ikkita alohida rol bor,
  haqiqiy POS operatorlari **`sotuvchi`** rolida (`kassir` rolidan hech kim
  foydalanmaydi). Har qanday yangi PIN/kassir-bog'liq `whereIn('rol', [...])`
  yozganda **`'sotuvchi'`ni albatta qo'shing** — bu xato PIN-terminalni
  haqiqiy kassirlar uchun butunlay ishlamas holga keltirgan edi (tuzatildi,
  commit `97bf290`, 3 joyda: admin blade, `PosTerminalController` 2 joy,
  `PosController::hisobotlar()`).
- **Terminal view'da kassir nomi** — har doim `$kassir` (PIN-sessiyadagi
  joriy foydalanuvchi, `PosTerminalController::index()` orqali uzatiladi)dan
  foydalaning, **`$smena->xodim`dan EMAS** (bu smenani ochgan kishi, joriy
  terminal-kassir emas) — aralashtirilgan xato bo'lgan (tuzatildi, `97bf290`).
- **Admin foydalanuvchilarning `filial_id`si `NULL` bo'lishi mumkin** — har
  qanday yangi PIN/filial-bog'liq so'rov yozganda buni eslab qoling
  (`->orWhere('rol', 'admin')` pattern'idan foydalaning, `PosTerminalController`
  ichida ikki joyda qo'llanilgan).
- **`/pos/tovarlar` va `/pos/saqlash`** — bu endpoint'lar PIN-sessiyaga
  bog'liq EMAS, `Auth::user()->filial_id`ga tayanadi. Agar kelajakda PIN
  bo'yicha xavfsizlikni kuchaytirish so'ralsa, bu ikkala endpoint'ni **diqqat
  bilan** qayta ko'rib chiqish kerak — ular hozir ham oddiy POS, ham
  fullscreen terminal tomonidan ishlatiladi.
- **Multi-barkod**: asosiy `tovar_katalog.barkod` ustuni hamon bitta va
  majburiy emas (avtomatik EAN-13 generatsiya bo'ladi). Qo'shimcha barkodlar
  `tovar_barkodlar` jadvalida, `cascade`da o'chadi. `PosController::tovarlar()`
  javobidagi `barkodlar_royxati` maydoniga JS shu asosda mos keladi — agar
  boshqa joyda ham `/pos/tovarlar` javobidan foydalanilsa, shu maydonni
  hisobga oling.
- **Migratsiya raqamlash** — ketma-ket, bo'sh joysiz davom eting (oxirgisi
  `062_tovar_barkodlar.php`). Fayl nomi format: `063_tavsif.php`.
- **`insertOrIgnore` dedup** — agar yangi jadvalga eski tizimdan import qilish
  kerak bo'lsa, UNIQUE index kerakligini unutmang (`eski_id` ustuni pattern'i
  — `054-056` migratsiyalarda qo'llanilgan).
- **Tinker orqali View render qilishda** `Undefined variable $errors` xatosi
  chiqishi mumkin — bu **haqiqiy xato emas** (`ShareErrorsFromSession`
  middleware tinker'da ishlamaydi). Haqiqiy tekshiruv uchun har doim
  qo'shimcha `curl` orqali yoki real brauzerda real marshrutni tekshiring.
- **Chrome MCP orqali test qilishda**: ba'zan `ref`ga asoslangan klik
  (`computer` tool, `ref` parametri) DOM elementida hech narsani ishga
  tushirmasligi mumkin (masalan, PIN klaviatura tugmalari, admin panel
  qatoridagi modal tugmalari) — sabab aniq emas. **Har doim koordinata
  bo'yicha klikdan keyin ekranni tekshiring** (screenshot yoki tegishli
  natija — masalan, modal ochilganini yoki dots to'lganini tasdiqlang);
  agar ishlamasa, xuddi shu koordinatada qayta urinib ko'ring yoki
  `form_input`/coordinate-click'ga o'ting.

## 7) Mavjud ishlayotgan funksiyalarni buzmaslik bo'yicha eslatmalar

- **Har doim**: LOCAL (`D:\ClaudeProjekt\adsmarket_sql\`) faylni tahrirlang →
  SSH orqali `php -l` bilan sintaksis tekshiring → keyin `scp` bilan deploy
  qiling. Hech qachon to'g'ridan-to'g'ri serverda faylni tahrirlamang.
- Har bir deploy'dan keyin: `php artisan view:cache && php artisan
  optimize:clear && chown -R www-data:www-data storage bootstrap/cache`.
- Migratsiyalarni ishga tushirishdan oldin **HAR DOIM** `php -l` bilan
  sintaksis tekshiring, keyin `php artisan migrate --force`.
- Har qanday yangi funksiyani **tinker** orqali (SQL/logika xatosiz
  ishlashini) VA **real brauzerda yoki curl** orqali (haqiqiy foydalanuvchi
  oqimi/HTTP marshrut) ikki bosqichda tasdiqlang — faqat bittasi yetarli emas.
  Backend-only (tinker/curl) test UI-daraja xatolarni (masalan, view'ga
  noto'g'ri o'zgaruvchi uzatilishi) qamrab olmaydi — bu 2 marta shu sababdan
  real xato o'tkazib yuborilgan (5.9-bo'lim).
- Test uchun yaratilgan barcha DB yozuvlarni (test smenalar, test qaytimlar,
  test PIN'lar, test audit-log yozuvlari, test barkodlar) **albatta tozalang**.
- `PosSmenaController::joriy()` — **yagona manba** ochiq smenani aniqlash
  uchun; yangi kod yozganda uni qayta ishlab chiqmang.
- Sidebar `$aktiv_grup` aniqlash blokidagi `elseif` zanjiri **tartib-bog'liq**
  — yangi guruh qo'shsangiz, mos `routeIs()` patternini to'g'ri joyga qo'ying
  (`layouts/app.blade.php`, ~385-qator atrofida).

---

## Git holati (server, `/var/www/adsmarket`)

- `bcef5d2` — Bosqich 1-4 (qism 1): POS Dashboard/hisobotlar, smena, qaytim,
  fullscreen PIN-terminal (backend).
- `97bf290` — PIN-terminal real-brauzer testida topilgan 2 ta xato tuzatildi
  (`sotuvchi` roli, terminal sarlavhasidagi kassir nomi).
- `d05b53c` — Multi-barkod (Bosqich 4, qism 2).

**POS bilan bog'liq bo'lmagan** (oldingi sessiyalardan, hali commit
qilinmagan): bonus tovar, etiketka shablon, hisobot konstruktor, balans,
kirim/chiqim hujjatlar refactor — bularning holati (tugallanganmi yoki yo'qmi)
hali tekshirilmagan. `migration_tmp/`, `routes/web.php.bak_etiketka`,
`storage/app/backup/`, `storage/app/existing_ids/` — untracked, POS'ga
aloqasi yo'q.

**Tavsiya**: bu boshqa ishlarni commit qilishdan oldin alohida ko'rib chiqing
— POS commit tarixini toza saqlash uchun ular bilan aralashtirmang.
