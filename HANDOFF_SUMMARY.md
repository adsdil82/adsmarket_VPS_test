# ADSmarket (polygon) — HANDOFF SUMMARY

> Server: `/var/www/adsmarket` (SSH: `nasiyapro`, config `D:/ClaudeProjekt/.ssh/config`)
> Local nusxa: `D:\ClaudeProjekt\adsmarket_sql\`
> Domen: `https://adsmarket.oilainvest.uz` (ichki tailscale IP: `100.77.240.39`)
> GitHub: `git@github-personal:adsdil82/adsmarket_VPS_test.git` (branch `main`)
> **Oxirgi commit**: `0353552` — "POS multi-barkod, AutoPay moduli, HibritPochta/SMS
> qayta yozish, Xodimlar ish haqi moduli va oy-tab dizayni" — **push qilingan**.
> Sana: 2026-07-13 (birinchi yaratilgan: 2026-07-07)

## 0. TEZKOR BOSHLASH (yangi sessiya shu yerdan boshlasin)

1. Bu faylni va [TODO_NEXT.md](TODO_NEXT.md)ni to'liq o'qing.
2. Git holati **toza** — `git status` bo'sh chiqishi kerak (agar chiqmasa,
   demak avvalgi sessiyada tugallanmagan ish bor, avval shuni tekshiring).
3. Deploy oqimi har doim shu tartibda: LOCAL faylni tahrirlash
   (`D:\ClaudeProjekt\adsmarket_sql\`) → `php -l` bilan sintaksis tekshirish →
   `scp` orqali serverga yuklash → `php artisan migrate --force` (agar
   migratsiya bo'lsa) → `php artisan optimize:clear && php artisan
   view:cache` → `chown -R www-data:www-data storage bootstrap/cache`.
4. Har bir o'zgarishdan keyin **hech bo'lmaganda** tinker orqali to'liq HTTP
   kernel simulyatsiyasi bilan tekshiring (pastda "Test metodologiyasi"
   bo'limiga qarang) — real login parolimiz yo'q, shuning uchun brauzerda
   to'g'ridan-to'g'ri UI test odatda **imkonsiz** (bo'lim 8ga qarang).
5. Ishni tugatgach: `git add -A` → `git status --short` bilan **albatta**
   tekshirib chiqing (sertifikat/backup/shaxsiy ma'lumot fayllari
   staged bo'lib qolmasin) → commit → `git push origin main` → shu ikki
   faylni (HANDOFF_SUMMARY.md, TODO_NEXT.md) yangilang.

---

## 1. Umumiy kontekst va modullar ro'yxati

Loyiha — **NasiyaPro/ADSmarket** nomli Laravel 11 kredit-savdo (POS + nasiya)
boshqaruv tizimi, "polygon" VPS muhitida joylashgan. Ushbu hujjat **barcha**
modullar bo'yicha to'plangan holat — har bir katta modul alohida bo'limda.

| # | Modul | Holat |
|---|---|---|
| 1 | POS (Kassa) — smena, qaytim, PIN-terminal, multi-barkod | ✅ Tugallangan, commit qilingan |
| 2 | AutoPay integratsiyasi | ✅ Tugallangan, commit qilingan |
| 3 | HibritPochta (pochta xabarnoma) qayta yozish | ✅ Tugallangan, commit qilingan |
| 4 | SMS moduli qayta yozish | ✅ Tugallangan, commit qilingan |
| 5 | **Xodimlar ish haqi (Payroll) moduli** | ✅ Tugallangan, commit qilingan |
| 6 | Chek/printer sozlamalari (POS spec qoldig'i) | ❌ Boshlanmagan |

**Muhim tamoyil butun loyiha davomida**: *"Mavjud ishlayotgan funksiyalarni
buzma."* Ko'p joyda refactor emas, duplikatsiya yo'li tanlangan (masalan,
`terminal/sotish.blade.php` — `ombor/pos/index.blade.php`ning mustaqil
nusxasi).

---

## 2. POS (Kassa) moduli — TO'LIQ TUGALLANGAN

> Batafsil texnik tafsilot juda katta bo'lgani uchun qisqartirilgan.
> To'liq versiya git tarixida saqlanadi (`bcef5d2`, `97bf290`, `d05b53c`
> commit xabarlarida va shu fayl 2026-07-08 versiyasida — `git log -p` bilan
> ko'rish mumkin).

- **Bosqich 1** — POS sidebar guruhi, POS Dashboard (`/pos/dashboard`), POS
  hisobotlar (Excel export).
- **Bosqich 2** — Kassir smenasi: `pos_smenalar` jadvali,
  `PosSmenaController::joriy()` — yagona manba ochiq smenani aniqlash uchun.
  Barcha POS savdolar endi ochiq smena talab qiladi.
- **Bosqich 3** — Qaytim/Vozvrat: ombor qoldig'ini tiklash, smena naqd
  balansini kamaytirish, `pul_oqimlari`ga chiqim yozish, ortiqcha
  qaytarishni bloklash (`PosTafsilot::qaytarilganMiqdor()`).
- **Bosqich 4/1** — Fullscreen PIN-kirish terminal: kassir tanlash + PIN
  klaviatura, bloklash (5 xato/15 daqiqa), qulflash/yechish, audit-log
  (`pos_terminal_loglar`). **Real brauzerda test qilingan**, 2 ta jiddiy xato
  topilib tuzatilgan (`sotuvchi` roli PIN tizimidan chetlangan edi; terminal
  sarlavhasi smena-ochuvchini ko'rsatardi, joriy PIN-kassirni emas).
- **Bosqich 4/2** — Multi-barkod: `tovar_barkodlar` jadvali (bitta tovarga
  ko'p shtrix-kod), `PosController::tovarlar()` javobiga `barkodlar_royxati`
  qo'shildi, ikkala ekranda (`ombor/pos/index.blade.php` va
  `terminal/sotish.blade.php`) JS barkod-qidiruv yangilandi.

**Muhim eslatmalar (hali ham amal qiladi)**:
- `Foydalanuvchi::$fillable`dan PIN maydonlarini (`pin_kod`, `pin_xato_soni`,
  `pin_bloklangan_gacha`) hech qachon olib tashlamang.
- Rol nomi **`sotuvchi`** (haqiqiy POS operatorlari), `kassir` rolidan hech
  kim foydalanmaydi — yangi PIN/kassir bog'liq so'rovlarda ikkalasini ham
  hisobga oling.
- Admin foydalanuvchilarning `filial_id`si `NULL` bo'lishi mumkin.
- `terminal/sotish.blade.php` va `ombor/pos/index.blade.php` — qasddan
  duplikatsiya, ikkalasini bir vaqtda yangilang.

**Boshlanmagan qoldiq**: chek/printer sozlamalari (`pos_printer_settings`,
`pos_receipt_templates`), POS umumiy sozlamalar sahifasi (auto-lock daqiqasi
hozircha `PosTerminalController::AUTO_LOCK_DAQIQA = 10` kodda hardcoded).

---

## 3. AutoPay integratsiyasi — TUGALLANGAN

Click/Payme uslubidagi tashqi to'lov tizimi (AutoPay) bilan integratsiya —
shartnoma yuborish, to'lovlarni avtomatik qabul qilish, sinxronlash.

**Jadvallar**: `066_autopay_shartnomalar.php`, `067_autopay_tranzaksiyalar.php`,
`069_autopay_shartnoma_ochirilgan_holat.php`,
`070_autopay_shartnoma_manba_va_nullable.php` (manba, pinfl ustunlari,
nullable FK), `071_autopay_kartalar.php`, `072_autopay_tranzaksiya_karta_ustunlari.php`.

**Modellar**: `AutopayShartnoma`, `AutopayTranzaksiya`, `AutopayKarta`.

**Controller**: `app/Http/Controllers/AutoPayController.php` — `index()`
**3 tabga** bo'lingan (Shartnomalar / Tranzaksiyalar / Kartalar + E-GOV),
bank-stil UI (`resources/views/autopay/index.blade.php`).

**Asosiy funksiyalar**:
- Webhook qabul qiluvchi endpoint va prepayment verification endpoint.
- `loan_id` formatiga shartnoma raqami qo'shildi.
- Checkbox orqali ko'p shartnomani birdaniga yuborish (bulk send).
- To'liq sinxronlash: barcha AutoPay kontrakt/tranzaksiyalarni olish,
  `tranzaksiyaniQayta()` — bog'lanmagan shartnoma uchun to'lov yozmaslik.
- Shartnomaga biriktirish modali + qidiruv endpoint.
- Tranzaksiyalar tabida davr bo'yicha sinxronlash, Manba ustuni, rang/filtr.
- Kartalar tabi (mijoz qidirish + `card.info`).
- E-GOV tabi (mijoz + xizmatlar + saqlash/olish/yangilash).
- Bulk contract metodlari (`createOrUpdate`/`bulk.update`/`bulk.delete`).
- To'lovni bekor qilish (`transaction.cancel`), noto'g'ri bog'langan 12 ta
  AutoPay to'lovi bekor qilingan (bir martalik tozalash, production'da
  bajarilgan).
- Shartnomani tahrirlash (`contract.update`) tugmasi.
- `app/Console/Commands/AutoPaySync.php` — buyruq orqali sinxronlash.

**Xizmat**: `app/Services/AutoPayService.php`.

**Real brauzerda test qilingan**: to'liq sinxronlash oqimi, shartnoma
biriktirish, bulk send.

---

## 4. HibritPochta va SMS modullari — QAYTA YOZILGAN

Eski `HybridMailController` + `xabarnoma/hybrid_mail/index.blade.php` va
eski SMS view'lar (`guruhli.blade.php`, `tarix.blade.php`, `yakka.blade.php`)
**butunlay o'chirilib**, yagona zamonaviy modullarga almashtirildi:

- `app/Http/Controllers/HibritPochtaController.php` +
  `resources/views/hibrit_pochta/index.blade.php` — bank-stil, yagona sahifa,
  tab asosida.
- `app/Http/Controllers/SmsController.php` (qayta yozilgan, +154 qator) +
  `resources/views/xabarnoma/sms/index.blade.php` (yagona, eski 3 ta alohida
  sahifa o'rniga).
- `app/Services/HybridPochtaService.php` kengaytirildi (+92 qator).

**Tuzatilgan xato (bu sessiyada)**: har ikkala yangi sahifada ham flash-xabar
(`session('muvaffaqiyat')`/`session('xato')`) **ikki marta** ko'rinardi —
sabab: `layouts/app.blade.php` global flash-xabar bloki bilan har bir sahifaning
o'z takroriy bloki bir vaqtda ishlagan. Har ikki sahifadan ham takroriy blok
olib tashlandi (`ish_haqi/index.blade.php`da ham xuddi shu xato topilib
tuzatilgan — bo'lim 5.6ga qarang).

---

## 5. Xodimlar ish haqi (Payroll) moduli — TO'LIQ YANGI, TUGALLANGAN

Foydalanuvchi so'rovi bo'yicha noldan qurilgan **to'liq mustaqil modul**:
xodimlarga oylik ish haqi hisoblash, oklad proporsional to'lov (davomatga
qarab), komissiya bonusi, savdo-reja bonusi, soliq/ushlanma, avans, va
tarix/dashboard hisobotlari. Alohida sidebar guruh + menyu, o'z rol/ruxsat
tizimi bilan (`ruxsat.check:xodimlar_ish_haqi`).

### 5.1 Ma'lumotlar bazasi (migratsiyalar `074`-`079`)

| Fayl | Vazifa |
|---|---|
| `074_xodimlar_ish_haqi_module.php` | `xodim_ish_haqi_sozlama` (oklad, bonus%, oylik reja), `xodim_davomat` (kunlik holat), `ish_haqi_hisoblari` (oylik hisob-kitob, snapshot maydonlari); "Ish haqi (avtomatik hisoblash)" harajat turi seed qilinadi |
| `075_davomat_oy_holati.php` | `davomat_oy_holati` — oy yopish/ochiq holati (unique yil+oy) |
| `076_ish_haqi_reja_min_max.php` | `reja_min_foizi` (default 80), `reja_max_foizi` (default 100) |
| `077_ish_haqi_bajarilish_foizi.php` | `ish_haqi_hisoblari.reja_bajarilish_foizi` |
| `078_ish_haqi_global_sozlama.php` | Singleton `ish_haqi_global_sozlama` (soliq 12%, boshqa ushlanma 0%) |
| `079_ish_haqi_avans_va_ustunlar.php` | `soliq_foizi`/`boshqa_ushlanma_foizi` (nullable, xodimga individual), `dastlabki_qoldiq`; `ish_haqi_hisoblari`ga soliq/ushlanma snapshot ustunlari; `ish_haqi_avanslar` jadvali |

### 5.2 Modellar (`app/Models/`)

- **`XodimIshHaqiSozlama`** — xodimga individual sozlama (oklad, bonus%,
  oylik reja, reja min/max%, soliq%/boshqa ushlanma% — **nullable**, `null`
  bo'lsa global sozlamadan olinadi, dastlabki qoldiq).
- **`XodimDavomat`** — kunlik davomat. `ICON_HOLATLARI` konstantasi har bir
  holat uchun ikonka/rang/nom beradi (keldi ✓ yashil, kelmadi ✗ qizil, kech
  qoldi, tatil, kasal, dam olish).
- **`IshHaqiHisob`** — oylik hisob-kitob natijasi, **barcha stavkalar
  hisoblash vaqtida shu qatorga "snapshot" qilinadi** (keyinchalik global/
  xodim sozlamasi o'zgarsa ham, eski oylar o'zgarmaydi).
  `qolganTolash(): float` — `jami_hisoblangan - avans_jami` (avansdan keyin
  qolgan to'lanadigan summa) — bu **hamma joyda** "qancha to'lash kerak"
  degan ma'noda ishlatiladi.
- **`IshHaqiGlobalSozlama`** — singleton, `::ol()` static metodi orqali
  olinadi (yo'q bo'lsa avtomatik yaratiladi).
- **`IshHaqiAvans`** — oy davomida berilgan avanslar tarixi.
- **`DavomatOyHolati`** — `::yopiqmi($yil, $oy)` — oy yopilganmi tekshiradi.
- **`Foydalanuvchi`**ga qo'shilgan relationlar: `ishHaqiSozlama()`,
  `davomatlar()`, `ishHaqiHisoblari()`, `ishHaqiAvanslari()`.

### 5.3 Hisoblash mantiqi — `app/Services/IshHaqiHisoblashService.php`

```
oklad_qismi = oklad * (kelgan_kunlar / ish_kunlari_jami)
komissiya_bonus = shu oyda shu xodimga tegishli shartnomalardan yig'ilgan
                  to'lovlarning bonus_foizi% (odatiy 5%)
reja_bonus = agar bajarilish% <= reja_min_foizi% → 0
             agar bajarilish% >= reja_max_foizi% → reja_bonus_summa (to'liq)
             oralig'ida → proporsional (chiziqli interpolatsiya):
             reja_bonus_summa * (bajarilish% - min%) / (max% - min%)
jami_gross = oklad_qismi + komissiya_bonus + reja_bonus + qoshimcha_hisoblash
soliq_summa = jami_gross * soliq_foizi% (xodimniki, bo'sh bo'lsa global 12%)
boshqa_ushlanma_summa = jami_gross * boshqa_ushlanma_foizi% (bo'sh bo'lsa global 0%)
jami_hisoblangan = jami_gross - soliq_summa - boshqa_ushlanma_summa - jarima(ushlanma)
qolganTolash = jami_hisoblangan - avans_jami (shu oyda berilgan avanslar yig'indisi)
```

- **`hisoblaOy()`** — bitta xodim uchun bitta oyni hisoblaydi/qayta hisoblaydi.
  Agar oy allaqachon `holat=tolandi` bo'lsa — **o'zgarmas** (qayta
  hisoblanmaydi).
- **`qoshimchaVaUshlanmaSaqla()`** — qo'lda kiritiladigan qo'shimcha bonus
  va jarima (ushlanma)ni saqlaydi, jami qayta hisoblanadi.
- **`avansBer()`** — avans berish: `DB::transaction` ichida `Harajat` yozuvi
  yaratiladi (`harajat_turi = "Ish haqi (avtomatik hisoblash)"`,
  `tegishli_xodim_id`), `TulovService::pulOqimigaYozKassaTuri(...)` orqali
  pul oqimiga **chiqim** yoziladi, `IshHaqiAvans` yozuvi yaratiladi va agar
  shu oy uchun hisob mavjud bo'lsa `avans_jami` qayta yig'iladi.
- **`tolash()`** — yakuniy to'lov: faqat **qolganTolash()** miqdorida (avans
  ayirilgan holda) Harajat+PulOqim yaratiladi (agar qolgan 0 yoki manfiy
  bo'lsa — Harajat yaratilmaydi), `holat=tolandi` qilinadi (shundan keyin
  **o'zgartirib bo'lmaydi**).

### 5.4 Controller — `app/Http/Controllers/IshHaqiController.php`

5 ta tab: `davomat`, `hisoblash`, `tarix`, `sozlamalar`, `dashboard` —
yagona `index($request)` metodida `$tab` query-param bo'yicha branch qilinadi
(shu loyihada barcha "bank-stil" modullar shu patternda: AutoPay,
HibritPochta ham shunday qurilgan).

- `davomatSaqla()` / `oyYopish()` — oylik davomat grid saqlash va oy yopish
  (`DavomatOyHolati::yopiqmi()` orqali tekshiriladi, yopilgan oyni tahrirlab
  bo'lmaydi).
- `sozlamaSaqla()` — xodimga individual sozlama (soliq%/boshqa ushlanma%
  bo'sh yuborilsa `NULL` saqlanadi → global qiymatdan foydalaniladi).
- `globalSozlamaSaqla()` — barcha xodimlar uchun global standart (soliq/
  boshqa ushlanma%).
- `avansBer()` — avans berish endpointi.
- `hisobla()` — "Hisoblash (barchasi)" tugmasi — tanlangan oy/filial bo'yicha
  barcha xodimlarni qayta hisoblaydi.

### 5.5 Sahifa dizayni — `resources/views/ish_haqi/index.blade.php`

**Davomat tabi**: oylik jadval — har bir kun ustun, har bir xodim qator,
har katakda icon-select (✓/✗/kech qoldi/tatil/kasal/dam olish). Oy yopish
tugmasi — yopilgandan keyin barcha select'lar `disabled`, avtomatik keyingi
oy ochiladi.

**Hisoblash tabi**: 3-guruhli jadval sarlavhasi (2 qatorli `<thead>`,
`rowspan`/`colspan` bilan):
- Yashil **HISOBLANDI** guruhi: Oklad qismi, Komissiya, Reja bonus(%), Qo'shimcha.
- Qizil **USHLANDI** guruhi: Jarima, Soliq, Boshqa ushlanma.
- Ko'k **TO'LANDI** guruhi: Avans, Yakuniy to'lov.
- Bundan tashqari: O'tgan oy qoldig'i (kulrang), Jami, Oy yakuniy qoldig'i
  (kulrang, `qolganTolash()` asosida hisoblanadi, oldingi oylardan qolgan
  to'lanmagan qoldiq + bir martalik "Dastlabki qoldiq" ham qo'shiladi).
- Har qatorda ikkita amal tugmasi: "Avans berish" (yashil, har doim) va
  tafsilot/hisoblash modali ("...", faqat hisob mavjud bo'lsa).

**Sozlamalar tabi**: yuqorida "Global sozlamalar" karta-forma (soliq%/boshqa
ushlanma%, barcha xodimlarga standart sifatida), pastda har bir xodim uchun
individual jadval (2-qatorli grouped header, "SHAXSIY STAVKA (bo'sh — global)"
guruhi), tahrirlash modali orqali oklad/bonus/reja/soliq/boshqa
ushlanma/dastlabki qoldiq belgilanadi.

**Bu sessiyada qo'shilgan — Oy-tab dizayni (bo'lim 6ga qarang)**: Davomat va
Hisoblash tablaridagi "Oy" `<select>` dropdown o'rniga, Pul Oqimi/AutoPay
uslubidagi **tab-qator** (Yan/Fev/Mar/.../Dek) qo'yildi — bitta qatorda,
bosilgan oy avtomatik faollashadi.

### 5.6 Tuzatilgan xatolar (bu modul qurilishi davomida)

1. **Flash-xabar 2 marta ko'rinishi** — `layouts/app.blade.php`da global
   render bor edi, `ish_haqi/index.blade.php` (va `hibrit_pochta`,
   `xabarnoma/sms`) o'zining alohida bloki bilan takrorlagan. Barchasidan
   takroriy blok olib tashlandi.
2. **Sozlamalar tabi 500 xatosi** (bu sessiyada topilib tuzatilgan) —
   `Attempt to read property "soliq_foizi" on null` — xodim jadvalidagi
   tahrirlash tugmasining `data-soliq-foizi="{{ $s->soliq_foizi }}"` va
   `data-boshqa-ushlanma-foizi="{{ $s->boshqa_ushlanma_foizi }}"` atributlari
   `?? ''` fallback'siz yozilgan edi (shu qatordagi boshqa barcha maydonlarda
   fallback bor edi, faqat shu ikkitasida yo'q edi) — xodim hali sozlama
   kiritmagan bo'lsa (`$s = null`) xato berardi. Tuzatildi:
   `{{ $s->soliq_foizi ?? '' }}`.

### 5.7 Real (tinker orqali to'liq HTTP-kernel) funksional test

To'liq end-to-end tekshirilgan: global sozlamalar merosxo'rligi (soliq=12%,
boshqa=0%), xodimga individual `dastlabki_qoldiq=100000`, `hisoblaOy()`
to'g'ri natija (`oklad_qismi=2000000`, `soliq_summa=240000`,
`jami=1760000`), `avansBer()` (500,000 berildi, `qolganTolash()=1260000`
to'g'ri hisoblandi, alohida Harajat yozuvi yaratildi), `tolash()` faqat
qolgan `1,260,000` uchun Harajat yaratdi (to'liq `1,760,000` emas). Barcha
test ma'lumotlari tozalangan.

**Barcha 5 tab** (`davomat`, `hisoblash`, `tarix`, `sozlamalar`, `dashboard`)
to'liq HTTP-kernel simulyatsiyasida `200 OK` qaytarishi tasdiqlangan.

---

## 6. Bu sessiyada bajarilgan qo'shimcha ish — Oy-tab dizayni

Foydalanuvchi so'rovi: Hisoblash va Davomat tablaridagi oy tanlash filtri
(`<select name="oy">`) ko'rinishini **tab-qator** (Pul Oqimi/AutoPay
modullaridagi kabi bitta qatorli navigatsiya uslubida) ko'rinishiga
o'tkazish, Yan/Fev/Mar/.../Dek tartibida, bosilgan oy avtomatik tanlanadi.

**O'zgarishlar** (`resources/views/ish_haqi/index.blade.php`):
- Yangi CSS: `.oy-tab-strip` (bitta qatorli konteyner, `overflow-x:auto`),
  `.oy-tab` (pill-uslubdagi havola), `.oy-tab.active` (ko'k gradient).
- Ikkala tabda (`davomat`, `hisoblash`) eski `<select name="oy">` olib
  tashlandi, o'rniga 12 ta `<a class="oy-tab">` havola qo'shildi — har biri
  `route('ish_haqi.index', array_merge(request()->except(['oy','page']),
  ['tab' => ..., 'oy' => $i+1]))` orqali **joriy barcha filtrlarni (yil,
  filial, qidiruv) saqlab qolgan holda** faqat `oy` parametrini almashtiradi.
- Filter-bar formasiga `<input type="hidden" name="oy" value="{{ $oy }}">`
  qo'shildi — Yil/Filial o'zgartirib "Ko'rish" bosilganda joriy oy saqlanib
  qoladi (chunki oy endi formaning o'zida emas, alohida tab-qatorda).

**Test qilingan**: to'liq HTTP-kernel simulyatsiyasi (haqiqiy login-sessiya
bilan, quyidagi bo'lim 8ga qarang) orqali tasdiqlangan — `yil=2026`,
`filial_id=3` kabi barcha parametrlar tab havolalarida to'g'ri saqlanib
qolmoqda, faqat bosilgan oy `active` klassi bilan to'g'ri belgilanmoqda.
Ikkala tab (`davomat`, `hisoblash`) uchun alohida tekshirilgan.

**Diqqat — bu o'zgarish oson qaytariladi**: agar yangi dizayn mos kelmasa,
`git revert` yoki `git log -p -- resources/views/ish_haqi/index.blade.php`
orqali eski `<select>` versiyasini tiklash mumkin (commit `0353552`dan
oldingi holat).

---

## 7. Git holati

```
fd9f773  Initial commit: ImkonPlus v1 — adsmarket polygon
9b92247  Kredit shartnoma formasi va hujjat tizimini kengaytirish
be2a51b  Shartnoma versiyasini qaytarish, DB/APP ZIP progress-bar, litsenziya
9581410  Bank-style UI redesign, payment page overhaul, reporting improvements
311a506  Bonus tovar, etiketka/hisobot shablonlari, balans va kirim-chiqim refaktori
bcef5d2  POS module phase 4 fullscreen PIN preparation
97bf290  Fix POS terminal: sotuvchi role excluded from PIN system, wrong cashier name
d05b53c  Add multi-barkod support for products (POS + fullscreen terminal)
c8a39c3  Update HANDOFF_SUMMARY.md and TODO_NEXT.md with browser-test findings
0353552  POS multi-barkod, AutoPay moduli, HibritPochta/SMS qayta yozish,
         Xodimlar ish haqi moduli va oy-tab dizayni  ← ENG OXIRGI
```

Barchasi `origin/main`ga push qilingan (`github-personal:adsdil82/adsmarket_VPS_test.git`).

**`.gitignore`ga qo'shilgan** (bu sessiyada, sensitive ma'lumotlarni himoya
qilish uchun): `/storage/app/certs/` (SSL sertifikat — `hp_cert.pfx`),
`/storage/app/backup/` (biznes ma'lumotlari backup JSON), `/storage/app/existing_ids/`
(mijozlar ro'yxati), `/migration_tmp/`, `*.bak_*`. **Bu papkalar hech qachon
git orqali commit qilinmasin** — ular real production ma'lumot va
sertifikatlarni o'z ichiga oladi.

---

## 8. Test metodologiyasi — MUHIM (nega browser test qilib bo'lmaydi)

Bu VPS'dagi `/ish-haqi` va boshqa admin-panel sahifalarini **haqiqiy
brauzerda** ko'rish uchun login parol kerak, lekin bazadagi parol **hash**
holida saqlanadi va uni tiklab bo'lmaydi — shuning uchun avtomatlashtirilgan
sessiyada odatiy login-forma orqali kirish **imkonsiz**.

**Ishlatilgan muqobil usul — to'liq HTTP-kernel simulyatsiyasi (tinker orqali)**:

```php
$app = app();
$store = $app['session']->driver();
$store->start();
$user = App\Models\Foydalanuvchi::find(13); // admin
Illuminate\Support\Facades\Auth::guard('web')->login($user);
$store->put('login_web_' . sha1(Illuminate\Auth\SessionGuard::class), $user->getAuthIdentifier());
$store->save();
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/ish-haqi', 'GET', ['tab' => 'hisoblash']);
$request->cookies->set(config('session.cookie'), $store->getId());
$response = $kernel->handle($request);
echo $response->getStatusCode(); // 200 kutilgan
$kernel->terminate($request, $response);
```

Bu usul **haqiqiy** middleware pipeline'ni (CSRF, session, `$errors` bag,
va h.k.) to'liq ishga tushiradi, shuning uchun `request()` global helper,
`session()`, `$errors` — barchasi **to'g'ri** ishlaydi (controller'ni
to'g'ridan-to'g'ri chaqirishdan farqli o'laroq — pastga qarang).

**ESLATMA — controller'ni to'g'ridan-to'g'ri chaqirish YETARLI EMAS**:
`app(IshHaqiController::class)->index($request)` orqali test qilish
tezroq, lekin **`request()` global helper eski/bo'sh so'rovni qaytaradi**
(chunki container'ga yangi `$request` bog'lanmagan) — bu Blade shablonidagi
`request()->except(...)` kabi joylarni **noto'g'ri** ko'rsatishi mumkin
(bu sessiyada oy-tab havolalarini tekshirishda aynan shu holat yuz berdi —
`yil` parametri "yo'qolganday" ko'rindi, aslida test metodologiyasi
kamchiligi edi). **Har doim to'liq kernel simulyatsiyasidan foydalaning**,
ayniqsa `request()`/`session()` global helper'lariga tayanadigan joylarni
tekshirganda.

**POST endpoint'lar uchun** (CSRF middleware bilan to'qnashadi): controller
metodini to'g'ridan-to'g'ri chaqirish qabul qilinadi (business-logikani
tekshirish uchun yetarli, CSRF himoyasining o'zini emas).

---

## 9. Umumiy arxitektura naqshlari (barcha yangi modullarda takrorlanadi)

- **Bank-stil yagona-sahifa modul**: bitta controller `index($tab)`
  branch qiladi, bitta Blade fayl `@if($tab === '...')` bloklari bilan,
  umumiy CSS (`.bank-table`, `.bank-wrap`, `.filter-bar`, `.badge-modern`),
  yuqorida `nav-tabs` orqali tab almashtirish.
- **Guruhlangan jadval sarlavhasi**: 2-qatorli `<thead>`, `rowspan`/`colspan`
  bilan, har guruh uchun rang-gradient CSS klassi.
- **Snapshot pattern**: hisoblash vaqtidagi stavkalar keyinchalik sozlama
  o'zgarsa ham o'zgarmasligi uchun natija jadvaliga saqlanadi.
- **Global + individual sozlama**: `$individual ?? $global` pattern —
  individual `NULL` bo'lsa global standart ishlatiladi.
- **Harajat/PulOqim yozish patterni**: `Harajat::create([...])` →
  `TulovService::pulOqimigaYozKassaTuri(filialId:, kassaTuri:, summa:,
  sana:, kategoriyaKodi:, izoh:, manbaTur:, manbaId:, yunalish:)`.

---

## 10. Xavfsizlik va maxfiylik eslatmalari

- `storage/app/certs/hp_cert.pfx` — HibritPochta SSL sertifikati, **hech
  qachon** git'ga qo'shilmasin (`.gitignore`da).
- `storage/app/backup/*.json`, `storage/app/existing_ids/*.txt` — real
  mijozlar/to'lovlar ma'lumoti, **hech qachon** git'ga qo'shilmasin.
- Har safar `git add -A` qilgandan keyin **albatta** `git status --short`
  bilan tekshiring — yangi sensitive fayl turi paydo bo'lsa `.gitignore`ga
  qo'shing, keyingina commit qiling.
