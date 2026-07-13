# TODO_NEXT.md — ADSmarket (polygon), keyingi sessiya uchun

> To'liq texnik tafsilot uchun qarang: [HANDOFF_SUMMARY.md](HANDOFF_SUMMARY.md)
> Eng oxirgi commit: `0353552` — "POS multi-barkod, AutoPay moduli,
> HibritPochta/SMS qayta yozish, Xodimlar ish haqi moduli va oy-tab dizayni"
> — **GitHub'ga push qilingan** (`origin/main`).

## 1) Nima bajarildi (barcha modullar, hozirgacha)

- **POS moduli** ✅ — sidebar guruhi, Dashboard, hisobotlar, kassir smenasi,
  qaytim/vozvrat, fullscreen PIN-terminal (brauzerda test qilingan),
  multi-barkod (brauzerda test qilingan).
- **AutoPay integratsiyasi** ✅ — shartnoma/tranzaksiya/karta, webhook,
  to'liq sinxronlash, E-GOV, bulk amallar, bank-stil 3-tabli UI (brauzerda
  test qilingan).
- **HibritPochta va SMS modullari** ✅ — eski kontroller/view'lar butunlay
  o'chirilib, yagona zamonaviy modullarga almashtirildi.
- **Xodimlar ish haqi (Payroll) moduli** ✅ — to'liq yangi modul: davomat
  (oylik grid + oy yopish), proporsional reja bonusi, global/xodim bo'yicha
  soliq/ushlanma sozlamalari, avans, hisoblash/tarix/dashboard tablari.
  To'liq HTTP-kernel simulyatsiyasida barcha 5 tab tekshirilgan, funksional
  test (hisoblash/avans/tolash) to'g'ri natija bergan.
- **Bu sessiyada qo'shimcha**: Ish haqi modulidagi Sozlamalar tabi 500
  xatosi tuzatildi (`$s->soliq_foizi` null-safe emas edi); Davomat/Hisoblash
  tablaridagi oy-select o'rniga tab-qator (Yan/Fev/.../Dek) dizayni
  qo'yildi; butun repo (78 fayl, ~9000 qator) commit qilinib GitHub'ga
  push qilindi; `.gitignore`ga sertifikat/backup/shaxsiy ma'lumot
  papkalari qo'shildi.

## 2) Nima test qilindi

- **POS/AutoPay**: real brauzerda (Chrome) to'liq test qilingan (tafsilot
  HANDOFF_SUMMARY.md 2-3-bo'limlarda).
- **Ish haqi moduli**: real login-parol yo'qligi sababli **to'liq HTTP-kernel
  simulyatsiyasi** (tinker orqali, haqiqiy sessiya-login bilan) orqali
  test qilindi — barcha 5 tab `200 OK`, funksional hisoblash/avans/tolash
  ssenariylari to'g'ri natija berdi. **Haqiqiy brauzerda vizual ko'rinish
  hali tasdiqlanmagan** — pastga, bo'lim 3ga qarang.
- Oy-tab havolalari real HTTP-kernel orqali tekshirildi: `yil`, `filial_id`
  kabi barcha joriy filtrlar saqlanib qolayotgani va `active` klass to'g'ri
  qo'yilayotgani tasdiqlangan (ikkala tab: `davomat`, `hisoblash`).

## 3) Nima hali tugallanmagan / ehtiyot choralar kerak

- ❌ **Ish haqi moduli — haqiqiy brauzerda vizual test qilinmagan**. Admin
  hisobining paroli hash holida saqlangani uchun avtomatlashtirilgan
  sessiyada login qilib bo'lmadi. Agar foydalanuvchi real brauzerda
  ko'rsatib bersa yoki vaqtinchalik test paroli bersa — quyidagilarni
  tekshirish kerak:
  - Oy-tab qatorining vizual ko'rinishi (rang, joylashuv, mobil ekranda
    `overflow-x:auto` to'g'ri ishlayaptimi).
  - Sozlamalar tabi — global karta + xodim jadvali, tahrirlash modali.
  - Hisoblash tabidagi 3-guruhli sarlavha (yashil/qizil/ko'k) to'g'ri
    ko'rinishi.
  - Davomat tabidagi icon-select'lar (rang/belgi) va oy yopish tugmasi.
- ❌ **Chek/printer sozlamalari** (`pos_printer_settings`,
  `pos_receipt_templates`) — POS spec'ning boshlanmagan qismi, hali
  tegilmagan.
- ❌ **POS umumiy sozlamalar sahifasi** (`pos_settings`) — auto-lock
  daqiqasi hozircha kodda hardcoded (`AUTO_LOCK_DAQIQA=10`).
- ⚠️ Ish haqi moduli — **Dashboard tabi** (5-tab) faqat "render xatosiz"
  darajasida tekshirilgan (200 OK), ichidagi grafik/statistika mantig'i
  chuqur funksional test qilinmagan.
- ✅ Git holati — **hammasi commit va push qilingan**, ishchi katalog toza
  bo'lishi kerak (`git status` bo'sh).

## 4) Keyingi sessiyada qaysi fayldan boshlash kerak

1. Avval **[HANDOFF_SUMMARY.md](HANDOFF_SUMMARY.md)**ni to'liq o'qing —
   barcha modullar bo'yicha texnik kontekst shu yerda.
2. `cd /var/www/adsmarket && git status` bilan boshlang — toza bo'lishi kerak.
3. Agar foydalanuvchi **ish haqi modulini haqiqiy brauzerda tekshirishni**
   so'rasa — bo'lim 3'dagi ro'yxatdan boshlang.
4. Agar **chek/printer sozlamalari**dan boshlanadigan bo'lsa: yangi
   migratsiya raqami `080` dan boshlanadi (oxirgisi
   `079_ish_haqi_avans_va_ustunlar.php`).
5. Agar **POS sozlamalari sahifasi**dan boshlanadigan bo'lsa: `pos_settings`
   jadvali, `admin_sozlamalar` patterniga o'xshab qiling.

## 5) Ehtiyot bo'lish kerak bo'lgan joylar (barcha modullar)

- **`terminal/sotish.blade.php` va `ombor/pos/index.blade.php`** — QASDDAN
  duplikatsiya, ikkalasini bir vaqtda yangilang.
- **`Foydalanuvchi::$fillable`** — PIN maydonlarini olib tashlamang.
- **Rol nomlari — `sotuvchi` vs `kassir`**: haqiqiy POS operatorlari
  `sotuvchi` rolida.
- **Admin foydalanuvchilarning `filial_id`si `NULL` bo'lishi mumkin.**
- **Ish haqi — snapshot pattern**: `ish_haqi_hisoblari` jadvalidagi
  soliq/bonus/ushlanma stavkalari hisoblash vaqtida "muzlatiladi" — global
  yoki xodim sozlamasini keyinroq o'zgartirish **eski oylarga ta'sir
  qilmaydi** (bu ataylab qilingan, xato emas).
- **Ish haqi — `holat=tolandi`**: bu oy uchun boshqa hech narsa
  o'zgartirilmaydi (hisoblash ham, ushlanma/qo'shimcha ham) — bu ham
  ataylab (to'lov tarixini himoya qilish uchun).
- **Tinker orqali View render qilishda** `Undefined variable $errors` va
  `request()->except()` **noto'g'ri natija** berishi mumkin — bu haqiqiy
  xato EMAS, controller'ni to'g'ridan-to'g'ri chaqirish (kernel'ni chetlab
  o'tish) natijasi. **Har doim to'liq HTTP-kernel simulyatsiyasidan
  foydalaning** (HANDOFF_SUMMARY.md bo'lim 8'dagi tayyor kod namunasi bilan).
- **`.gitignore`da endi bor**: `/storage/app/certs/`, `/storage/app/backup/`,
  `/storage/app/existing_ids/`, `/migration_tmp/`, `*.bak_*` — bu papkalar
  **hech qachon** `git add`ga qo'shilmasin (sertifikat + real mijoz
  ma'lumotlari). Har `git add -A`dan keyin `git status --short` bilan
  albatta tekshiring.
- **Migratsiya raqamlash** — ketma-ket davom eting, oxirgisi
  `079_ish_haqi_avans_va_ustunlar.php`, keyingisi `080_...`.

## 6) Mavjud ishlayotgan funksiyalarni buzmaslik bo'yicha eslatmalar

- **Har doim**: LOCAL (`D:\ClaudeProjekt\adsmarket_sql\`) faylni tahrirlang →
  SSH orqali `php -l` bilan sintaksis tekshiring → keyin `scp` bilan deploy
  qiling. Hech qachon to'g'ridan-to'g'ri serverda faylni tahrirlamang.
- Har bir deploy'dan keyin: `php artisan optimize:clear && php artisan
  view:cache && chown -R www-data:www-data storage bootstrap/cache`.
- Migratsiyalarni ishga tushirishdan oldin **HAR DOIM** `php -l` bilan
  sintaksis tekshiring, keyin `php artisan migrate --force`.
- Har qanday yangi funksiyani **tinker** orqali (to'liq HTTP-kernel
  simulyatsiyasi bilan, yuqoridagi eslatmaga qarang) VA imkon bo'lsa real
  brauzerda tasdiqlang.
- Test uchun yaratilgan barcha DB yozuvlarini **albatta tozalang**.
- Ishni tugatgach: `git add -A` → `git status --short` bilan tekshirish →
  commit → `git push origin main` → HANDOFF_SUMMARY.md va TODO_NEXT.md'ni
  yangilash — bu **standart yakunlash protokoli**, har sessiyada takrorlang.

---

## Git holati (server, `/var/www/adsmarket`)

```
fd9f773 → 9b92247 → be2a51b → 9581410 → 311a506 → bcef5d2 → 97bf290
→ d05b53c → c8a39c3 → 0353552 (HEAD, origin/main bilan sinxron)
```

Barcha commit'lar push qilingan, ishchi katalog toza. Keyingi sessiya
yangi ish boshlashdan oldin `git status`ni tekshirsin.
