# TODO_NEXT.md — POS moduli, keyingi sessiya uchun

> To'liq texnik tafsilot uchun qarang: [HANDOFF_SUMMARY.md](HANDOFF_SUMMARY.md)

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
  (`pos_terminal_loglar`), admin panelda PIN o'rnatish UI.

## 2) Nima test qilindi

- Barcha backend logika **tinker** orqali funksional test qilindi (PIN
  hashing, bloklash, to'liq terminal oqimi: kirish→qulflash→yechish→chiqish).
- Barcha yangi/o'zgargan marshrutlar **curl** orqali tekshirildi — `302`
  (login redirect), `500` yo'q, `laravel.log`da yangi xato yo'q.
- Qaytim modulida to'liq end-to-end ssenariy: sotuv → qisman qaytim → ombor/
  smena/pul-oqimi muvofiqligi → ortiqcha qaytarish bloklanishi.
- **Barcha test ma'lumotlari tozalangan** (test PIN'lar, test smenalar, test
  qaytimlar, `pos_terminal_loglar` test yozuvlari) — production'ga ta'sir yo'q.

## 3) Nima hali tugallanmagan

- ❌ **Real brauzerda UI test qilinmagan** — PIN klaviaturasi, qulflash
  overlay, avtomatik-qulflash timer, "boshqa kassir aniqlandi" holatida
  `location.reload()` xatti-harakati — faqat backend darajasida tekshirilgan.
- ❌ **Multi-barkod** (`product_barcodes` jadvali) — boshlanmagan.
- ❌ **Chek/printer sozlamalari** (`pos_printer_settings`,
  `pos_receipt_templates`) — boshlanmagan.
- ❌ **POS sozlamalari sahifasi** (`pos_settings`) — boshlanmagan; hozircha
  auto-lock daqiqasi (`PosTerminalController::AUTO_LOCK_DAQIQA = 10`) kodda
  hardcoded.
- ⚠️ Server repo'da **hech narsa commit qilinmagan** — bu sessiyaning POS
  ishlari + oldingi sessiyalarning boshqa tugallangan/tugallanmagan ishlari
  (bonus tovar, etiketka shablon, balans, kirim/chiqim refactor) hammasi
  working tree'da turibdi (`git status` — quyida).

## 4) Keyingi sessionda qaysi fayldan boshlash kerak

1. Avval **[HANDOFF_SUMMARY.md](HANDOFF_SUMMARY.md)**ni o'qing — to'liq
   texnik kontekst shu yerda.
2. Agar **UI/browser test** kerak bo'lsa: `https://adsmarket.oilainvest.uz/terminal/pin`
   dan boshlang (avval `admin/foydalanuvchilar` sahifasida biror kassirga PIN
   o'rnatish kerak).
3. Agar **multi-barkod**dan boshlanadigan bo'lsa:
   - Yangi migratsiya: `062_product_barcodes.php` (raqamlash davomiyligini
     saqlang — oxirgisi `061`).
   - `TovarKatalog` modeliga `barkodlar()` hasMany relation.
   - `PosController::tovarlar()` (AJAX qidiruv) va
     `PosTerminalController`dagi barkod-skan logikasini (`terminal/sotish.blade.php`
     JS: `barkodSkan()`) yangi jadvalga moslashtirish kerak — **ehtiyot bo'ling**,
     bu funksiya hozir ishlaydi va ikkita joyda (oddiy POS + fullscreen
     terminal) duplikatsiya qilingan, ikkalasini ham yangilash kerak bo'ladi.
4. Agar **chek/printer sozlamalari**dan boshlanadigan bo'lsa:
   - Yangi migratsiya raqami: `062` yoki multi-barkoddan keyin `063`.
   - `PosController::chekKorish()` — hozirgi chek ko'rinishini o'rganib chiqing.

## 5) POS fullscreen PIN login / lock-unlock / multi-barkod / chek-printer — qolgan ishlar

| Bo'lim | Holat | Izoh |
|---|---|---|
| PIN login (kassir tanlash + klaviatura) | ✅ Tugallandi | `terminal/pin.blade.php`, `PosTerminalController::pinForma/pinTekshir` |
| Bloklash (5 xato/15 daqiqa) | ✅ Tugallandi | `Foydalanuvchi::pinBloklanganmi()` |
| Lock/Unlock (qulflash/yechish) | ✅ Backend tugallandi, ⚠️ UI test qilinmagan | `PosTerminalController::qulflash/yechish` |
| Avtomatik qulflash (inaktivlik timeri) | ✅ Kod yozildi, ⚠️ real vaqt sinovi yo'q | JS: `terminal/sotish.blade.php` → `faollikKuzatish()`, hozircha 10 daqiqa hardcoded |
| Kassir almashtirish (boshqa PIN bilan unlock) | ✅ Backend tugallandi, ⚠️ UI test yo'q | `yechish()` → `boshqa_kassir:true` → JS `location.reload()` |
| Audit-log (`pos_terminal_loglar`) | ✅ Tugallandi | `kirish/xato_pin/bloklandi/qulflash/yechish/chiqish` hodisalari |
| Multi-barkod | ❌ Boshlanmagan | Spec: `product_barcodes` jadvali kerak |
| Chek shabloni sozlamalari | ❌ Boshlanmagan | `pos_receipt_templates` |
| Printer sozlamalari | ❌ Boshlanmagan | `pos_printer_settings` |
| POS umumiy sozlamalar sahifasi | ❌ Boshlanmagan | `pos_settings`, auto-lock daqiqasini shu yerdan boshqarish kerak |

## 6) Ehtiyot bo'lish kerak bo'lgan joylar

- **`terminal/sotish.blade.php` va `ombor/pos/index.blade.php` — QASDDAN
  duplikatsiya qilingan** (refaktor emas). Agar kelajakda savat/to'lov
  logikasiga o'zgartirish kiritilsa, **IKKALASINI HAM** yangilash kerak,
  aks holda ikki ekran orasida nomuvofiqlik paydo bo'ladi.
- **`Foydalanuvchi::$fillable`** — PIN bilan bog'liq maydonlarni (`pin_kod`,
  `pin_xato_soni`, `pin_bloklangan_gacha`) olib tashlamang — bu sessiyada
  ularning yo'qligi sabab jiddiy "jim" xato bo'lgan edi (pastga qarang).
- **Admin foydalanuvchilarning `filial_id`si `NULL` bo'lishi mumkin** — har
  qanday yangi PIN/filial-bog'liq so'rov yozganda buni eslab qoling
  (`->orWhere('rol', 'admin')` pattern'idan foydalaning, `PosTerminalController`
  ichida ikki joyda qo'llanilgan).
- **`/pos/tovarlar` va `/pos/saqlash`** — bu endpoint'lar PIN-sessiyaga
  bog'liq EMAS, `Auth::user()->filial_id`ga tayanadi. Agar kelajakda PIN
  bo'yicha xavfsizlikni kuchaytirish so'ralsa, bu ikkala endpoint'ni **diqqat
  bilan** qayta ko'rib chiqish kerak — ular hozir ham oddiy POS, ham
  fullscreen terminal tomonidan ishlatiladi.
- **Migratsiya raqamlash** — ketma-ket, bo'sh joysiz davom eting (oxirgisi
  `061`). Fayl nomi format: `062_tavsif.php` (avvalgi sessiyada
  `2025_01_01_000060_...` formatida noto'g'ri yaratilib, keyin to'g'irlangan
  xato bo'lgan — **standart formatdan chetga chiqmang**).
- **`insertOrIgnore` dedup** — agar yangi jadvalga eski tizimdan import qilish
  kerak bo'lsa, UNIQUE index kerakligini unutmang (`eski_id` ustuni pattern'i
  — `054-056` migratsiyalarda qo'llanilgan).
- **Tinker orqali View render qilishda** `Undefined variable $errors` xatosi
  chiqishi mumkin — bu **haqiqiy xato emas** (`ShareErrorsFromSession`
  middleware tinker'da ishlamaydi). Haqiqiy tekshiruv uchun har doim
  qo'shimcha `curl` orqali real marshrutni tekshiring.

## 7) Mavjud ishlayotgan funksiyalarni buzmaslik bo'yicha eslatmalar

- **Har doim**: LOCAL (`D:\ClaudeProjekt\adsmarket_sql\`) faylni tahrirlang →
  SSH orqali `php -l` bilan sintaksis tekshiring → keyin `scp` bilan deploy
  qiling. Hech qachon to'g'ridan-to'g'ri serverda faylni tahrirlamang.
- Har bir deploy'dan keyin: `php artisan view:cache && php artisan
  optimize:clear && chown -R www-data:www-data storage bootstrap/cache`.
  **Diqqat**: `optimize:clear` `view:cache`dan KEYIN chaqirilsa, view cache'ni
  tozalab qo'yadi — agar `route:cache` ham kerak bo'lsa, buyruqlar tartibini
  tekshiring (bu sessiyada shu sabab view cache'ni ikki marta qayta qurishga
  to'g'ri keldi).
- Migratsiyalarni ishga tushirishdan oldin **HAR DOIM** `php -l` bilan
  sintaksis tekshiring, keyin `php artisan migrate --force`.
- Har qanday yangi funksiyani **tinker** orqali (SQL/logika xatosiz
  ishlashini) VA **curl** orqali (real HTTP marshrut, `302`/`200`, `500` yo'q,
  `laravel.log` bo'sh) ikki bosqichda tasdiqlang — faqat bittasi yetarli emas.
- Test uchun yaratilgan barcha DB yozuvlarni (test smenalar, test qaytimlar,
  test PIN'lar, test audit-log yozuvlari) **albatta tozalang** — bu
  sessiyada har bir test tsiklidan keyin qat'iy amal qilingan tamoyil.
- `PosSmenaController::joriy()` — **yagona manba** ochiq smenani aniqlash
  uchun; yangi kod yozganda uni qayta ishlab chiqmang, shu metoddan
  foydalaning (`PosController`, `PosTerminalController` shunday qiladi).
- Sidebar `$aktiv_grup` aniqlash blokidagi `elseif` zanjiri **tartib-bog'liq**
  — yangi guruh qo'shsangiz, mos `routeIs()` patternini to'g'ri joyga qo'ying
  (`layouts/app.blade.php`, ~385-qator atrofida).

---

## Git holati (server, `/var/www/adsmarket`)

`git status` natijasi — quyida asosiy suhbatda ko'rsatilgan. Xulosa:

- **POS bilan bog'liq**: 11 ta yangi (untracked) fayl + 2 ta yangi papka
  (`ombor/pos/qaytim/`, `ombor/pos/smena/`, `terminal/`), 11 ta o'zgartirilgan
  fayl.
- **POS bilan bog'liq emas** (oldingi sessiyalardan): bonus tovar, etiketka
  shablon, hisobot konstruktor, balans, kirim/chiqim hujjatlar refactor —
  bularning holati (tugallanganmi yoki yo'qmi) ushbu sessiyada tekshirilmadi.

**Tavsiya**: commit qilishdan oldin, POS bilan bog'liq bo'lmagan
o'zgarishlarni **alohida commit**da ajrating (agar ular haqiqatan tugallangan
bo'lsa) — POS commit tarixini toza saqlash uchun. Agar vaqt yo'q bo'lsa, hech
bo'lmasa commit **message**da ikkala guruhni aniq ajratib yozing (asosiy
suhbatda taklif qilingan commit message shunday tuzilgan).
