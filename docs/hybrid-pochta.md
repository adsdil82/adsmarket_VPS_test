# Hybrid Pochta (hybrid.pochta.uz) — Modul Hujjatnamasi

## Maqsad

Kechikkan kreditlar bo'yicha mijozlarga **jismoniy pochta xati** yuborish.
Tizim PDF xat yaratadi → Hybrid Pochta API ga yuboradi → ular bosib chiqarib, mijoz uyiga eltib beradi.

---

## Fayl strukturasi

```
app/
├── Services/HybridPochtaService.php       — asosiy API servis
├── Http/Controllers/
│   ├── GibridPochtaController.php         — test-conn, regions, areas, log jurnali
│   └── PochtaShablonController.php        — shablon CRUD

app/Models/
├── PochtaShablon.php                      — pochta_shablonlar jadvali
└── PochtaLog.php                          — pochta_loglar jadvali

database/migrations/
└── 022_hybrid_pochta.php                  — 2 ta jadval

resources/views/
├── malumotnamalar/pochta-shablonlar/
│   ├── index.blade.php                    — shablon boshqaruvi
│   └── _form.blade.php                    — forma partial
├── admin/pochta-loglar/
│   └── index.blade.php                    — log jurnali
└── pochta/
    └── xat_shablon.blade.php              — PDF Blade template (DomPDF)

docs/
└── hybrid-pochta.md                       — bu fayl
```

---

## Ma'lumotlar bazasi

### `pochta_shablonlar`
| Ustun | Tur | Izoh |
|---|---|---|
| nomi | varchar(100) | Shablon nomi |
| matn | text | Xat matni ({{ozgaruvchi}} lar bilan) |
| qayta_yuborish_kun | int | Min kunlar oralig'i (0 = cheklovsiz) |
| holat | enum(faol/nofaol) | |
| sort_order | int | Ko'rsatish tartibi |

### `pochta_loglar`
| Ustun | Tur | Izoh |
|---|---|---|
| reg_kredit_id | FK | Kredit |
| mijoz_id | FK | Mijoz |
| shablon_id | FK nullable | Ishlatilingan shablon |
| api_letter_id | int nullable | Hybrid Pochta Letter ID |
| receiver | varchar(200) | FIO |
| address | text | To'liq pochta manzili |
| region_id | int nullable | API viloyat ID |
| area_id | int nullable | API tuman ID |
| holat | enum | kutilmoqda/yaratildi/yuborildi/xato |
| so_rov | json | API ga yuborilgan so'rov (debug) |
| javob | json | API javobi |
| yaratildi_vaqt | timestamp | createMail vaqti |
| yuborildi_vaqt | timestamp | sendMail vaqti |

---

## Sozlamalar (sozlamalar jadvali)

| Kalit | Izoh |
|---|---|
| hybrid_pochta_login | API login |
| hybrid_pochta_password | API parol |
| hybrid_pochta_yoqilgan | 0/1 (on/off) |
| hybrid_pochta_region_id | Tashkilot viloyat ID |
| hybrid_pochta_area_id | Tashkilot tuman ID |
| hybrid_pochta_cert_parol | (Variant B) sertifikat paroli |

---

## Shablon o'zgaruvchilari

| O'zgaruvchi | Ma'no |
|---|---|
| `{{mijoz_fio}}` | Mijoz to'liq ismi |
| `{{shartnoma_raqam}}` | Shartnoma raqami |
| `{{kechikish_kun}}` | Kechikish kunlari soni |
| `{{jami_qarz}}` | Qoldiq qarz (so'm) |
| `{{yuborish_sana}}` | Xat yuborilgan sana (d.m.Y) |
| `{{tashkilot_nomi}}` | Tashkilot nomi (sozlamalardan) |

---

## API oqimi

### Variant A — Brauzer E-IMZO (asosiy)

```
1. Client → POST /kreditlar/{id}/pochta/create  (Phase 2/3 da qo'shiladi)
   Server → HybridPochtaService::generatePdfBase64()
          → HybridPochtaService::createMail()         → api_letter_id
          → HybridPochtaService::getHashForSign()     → hashcode
   Server → JSON: { letter_id, hash }

2. Brauzer → EIMZOClient.createPkcs7(keyId, hash, null, callback)
             → signature (PKCS7 base64)

3. Client → POST /kreditlar/{id}/pochta/send
            Body: { letter_id, signature }
   Server → HybridPochtaService::sendMailVariantA()  → muvaffaqiyat
          → PochtaLog yoziladi
```

### Variant B — Server sertifikat (brauzersiz)

```
Sozlash:
  1. E-IMZO dan sertifikat .pfx formatida eksport qilish
  2. VPS ga yuklash: storage/app/certs/hp_cert.pfx
  3. Sozlamalar da: hybrid_pochta_cert_parol = <sertifikat paroli>

Oqim:
  Server → HybridPochtaService::sendMailVariantB(letterId)
         → getHashForSign() → hash
         → openssl_pkcs7_sign() → PKCS7 signature
         → sendMailVariantA() → muvaffaqiyat
```

**Eslatma:** Variant B uchun E-IMZO PKCS7 formati OpenSSL PKCS7 dan farq qilishi mumkin.
Agar imzo rad etilsa — Hybrid Pochta texnik jamoasi bilan PKCS7 format talablarini aniqlang.

---

## Token boshqaruvi

- Token 7 kun amal qiladi, **6 kun** Cache da saqlanadi (`hybrid_pochta_token`)
- 401 response → avtomatik token yangilash + qayta urinish
- `testConnection()` → tokenni tozalab, yangi token oladi (sozlamalar tekshirish uchun)
- Spravochniklar (regions/areas) — 24 soat cache

---

## Routes (routes/web.php)

```
GET  /malumotnamalar/pochta-shablonlar          → PochtaShablonController@index
POST /malumotnamalar/pochta-shablonlar          → PochtaShablonController@store
PUT  /malumotnamalar/pochta-shablonlar/{id}     → PochtaShablonController@update
DEL  /malumotnamalar/pochta-shablonlar/{id}     → PochtaShablonController@destroy

POST /admin/gibrid-pochta/test-connection       → GibridPochtaController@testConnection
GET  /admin/gibrid-pochta/regions               → GibridPochtaController@regions
GET  /admin/gibrid-pochta/areas                 → GibridPochtaController@areas
GET  /admin/gibrid-pochta/loglar                → GibridPochtaController@loglar
```

---

## Bosqichlar

| Bosqich | Holat | Tarkib |
|---|---|---|
| **1 — Asos** | ✅ Amalga oshirildi | Migration, Models, Service, Controller, Views, Sozlamalar |
| **2 — Shablon** | Keyingi | PDF template yaxshilash, ko'rinish |
| **3 — Yuborish** | Keyingi | Kredit sahifasi widget, E-IMZO JS, send flow |
| **4 — Log UI** | Keyingi | Log filtrlash yaxshilash, kvitansiya yuklab olish |

---

## Muhim eslatmalar

1. **HybridMailController** (mavjud) — bu boshqa narsa: email notification channel.
   **GibridPochtaController** — jismoniy pochta (hybrid.pochta.uz).
   Ikkisi bir xil emas!

2. **E-IMZO** faqat brauzerda ishlaydigan plugin. Server side uchun Variant B.

3. Har bir xat yuborishda `PochtaLog` yoziladi — muvaffaqiyatli bo'lmasa ham.

4. `qayta_yuborish_kun = 0` → cheklovsiz qayta yuborish mumkin.

5. Token cache key: `hybrid_pochta_token` (Cache::forget orqali tozalash mumkin).


---

## Bosqich 3 - Xat Yuborish Oqimi (Phase 3)

### Yangi fayllar

- PochXatController.php: create(), send(), preview() AJAX endpoints
- kredit/_pochta_tab.blade.php: Kredit sahifasidagi Pochta tab kontenti
- kredit/_pochta_modal.blade.php: Kop qadamli yuborish modali + E-IMZO JS

### Ozgartirilgan fayllar

- routes/web.php: kreditlar/{kredit}/pochta/{create,send,preview} routelari
- RegKreditController.php: show() ga hp_yoqilgan, pochta_shablonlar, pochta_loglar
- kredit/show.blade.php: Xat yuborish tugma + Pochta tab + modal include

### Routes

- POST kreditlar/{kredit}/pochta/create  - kreditlar.pochta.create
- POST kreditlar/{kredit}/pochta/send    - kreditlar.pochta.send
- GET  kreditlar/{kredit}/pochta/preview - kreditlar.pochta.preview

### Kredit sahifasi oqimi

1. Tugma - muddati_otgan yoki tugash_sana < bugun bo'lsa kechikkan uchun korsatiladi
2. Qadam 1 - Shablon, FIO, manzil, viloyat/tuman (API dan yuklanadi)
3. AJAX create - PDF yaratish, API da xat, hash olish, log yaratiladi
4. Qadam 2 - E-IMZO plugin (127.0.0.1:64443) sertifikat tanlab imzolash
5. AJAX send - imzo bilan API ga yuborish
6. Natija - muvaffaqiyat/xato ekrani, log yangilanadi holat=yuborildi
