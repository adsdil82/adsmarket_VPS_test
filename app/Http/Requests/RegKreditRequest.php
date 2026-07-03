<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegKreditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->rol, ['admin', 'menejer']);
    }

    public function rules(): array
    {
        return [
            'mijoz_id'            => ['required', 'exists:mijozlar,id'],
            'filial_id'           => ['required', 'exists:filiallar,id'],
            'joriy_xodim_id'      => ['nullable', 'integer', 'exists:foydalanuvchilar,id'],

            // Moliyaviy ma'lumotlar
            'jami_summa'          => ['required', 'numeric', 'min:0'],
            'boshlangich_tolov'   => ['required', 'numeric', 'min:0'],
            'muddati_oy'          => ['required', 'integer', 'min:1', 'max:36'],
            'tolov_kuni'          => ['required', 'integer', 'min:1', 'max:31'],
            'foiz_stavka'         => ['nullable', 'numeric', 'min:0', 'max:100'],

            // prepareForValidation() orqali hisoblanadigan maydonlar.
            // Bular rules() ichida bo'lmasa, validated() ularni qaytarmaydi.
            'kredit_summa'        => ['required', 'numeric', 'min:0'],
            'qoldiq_qarz'         => ['required', 'numeric', 'min:0'],
            'oylik_tolov_miqdori' => ['required', 'numeric', 'min:0'],
            'tolov_qilingan'      => ['required', 'numeric', 'min:0'],

            // Sanalar
            'boshlanish_sana'     => ['required', 'date'],
            'tugash_sana'         => ['required', 'date', 'after:boshlanish_sana'],

            // Kafil (ixtiyoriy)
            'kafil_mijoz_id'      => ['nullable', 'integer', 'exists:mijozlar,id'],
            'kafil_ism'           => ['nullable', 'string', 'max:200'],
            'kafil_telefon'       => ['nullable', 'string', 'max:50'],
            'kafil_manzil'        => ['nullable', 'string'],

            'izoh'                => ['nullable', 'string'],

            // Tovarlar (kamida 1 ta kerak)
            'tovarlar'            => ['required', 'array', 'min:1'],
            'tovarlar.*.nomi'     => ['required', 'string', 'max:300'],
            'tovarlar.*.soni'     => ['required', 'integer', 'min:1'],
            'tovarlar.*.narx'     => ['required', 'numeric', 'min:0'],
            'tovarlar.*.barkod'          => ['nullable', 'string', 'max:100'],
            'tovarlar.*.tovar_katalog_id' => ['nullable', 'integer'],

            // To'lov grafigi (ixtiyoriy — kiritilmasa kontroller avtomatik generatsiya qiladi)
            'grafik'              => ['nullable', 'array'],
            'grafik.*.sana'       => ['required_with:grafik', 'date'],
            'grafik.*.summa'      => ['required_with:grafik', 'numeric', 'min:0'],
            'grafik.*.ustama'     => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'mijoz_id.required'          => 'Mijoz tanlanishi shart.',
            'jami_summa.required'        => 'Jami summa kiritilishi shart.',
            'boshlangich_tolov.required' => 'Boshlang\'ich to\'lov kiritilishi shart.',
            'muddati_oy.required'        => 'Muddat (oy) kiritilishi shart.',
            'muddati_oy.max'             => 'Muddat 36 oydan oshmasligi kerak.',
            'boshlanish_sana.required'   => 'Boshlanish sanasi kiritilishi shart.',
            'tugash_sana.after'          => 'Tugash sanasi boshlanish sanasidan keyin bo\'lishi kerak.',
            'tovarlar.required'          => 'Kamida 1 ta tovar kiritilishi shart.',
            'tovarlar.*.nomi.required'   => 'Tovar nomi kiritilishi shart.',
            'tovarlar.*.soni.required'   => 'Tovar soni kiritilishi shart.',
            'tovarlar.*.narx.required'   => 'Tovar narxi kiritilishi shart.',
        ];
    }

    /** Hisoblangan maydonlarni qo'shish */
    protected function prepareForValidation(): void
    {
        // jami_summa — klient tomonidan allaqachon "tovar summasi + ustama" sifatida
        // hisoblab yuborilgan umumiy shartnoma summasi (qarang: kredit/_form_tabs.blade.php
        // hisoblash()). Bu yerda ustamani QAYTA qo'shib yubormaslik kerak — aks holda
        // ustama ikki marta hisoblanib ketadi.
        $jami     = $this->jami_summa ?? 0;
        $oldin    = $this->boshlangich_tolov ?? 0;
        $kredit   = max(0, $jami - $oldin);
        $muddati  = $this->muddati_oy ?? 1;

        $oylik = $muddati > 0 ? round($kredit / $muddati, 2) : 0;

        $this->merge([
            'kredit_summa'        => $kredit,
            'qoldiq_qarz'         => $kredit,
            'oylik_tolov_miqdori' => $oylik,
            'tolov_qilingan'      => 0,
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Operatsion kun nazorati — orqaga sanali shartnoma faqat "admin" uchun ochiq.
            // "menejer" rol ham shartnoma tuza oladi, lekin sozlama yoqilgan bo'lsa o'tgan
            // kunga sana qo'ya olmaydi (orqa operatsiyalar admin ruxsati bilan).
            if (\App\Models\Sozlama::ol('orqaga_sana_taqiqlansin', '1') === '1'
                && $this->user()?->rol !== 'admin'
                && $this->boshlanish_sana) {
                $bosh = \Illuminate\Support\Carbon::parse($this->boshlanish_sana)->startOfDay();
                if ($bosh->lt(now()->startOfDay())) {
                    $validator->errors()->add(
                        'boshlanish_sana',
                        "Faqat admin o'tgan kunga shartnoma sanasini qo'yishi mumkin. Iltimos, bugungi yoki kelajakdagi sanani tanlang."
                    );
                }
            }

            // Grafik: ketma-ket to'lov sanalari orasidagi farq ~1 oydan (31 kun) oshmasligi
            // kerak. Eslatma: standart oylik grafikda (har oyning bir xil kunida) taqvim oyi
            // uzunligiga qarab farq 28-31 kun bo'lishi mumkin — shu sababli chegara 31 kun
            // qilib olingan, aks holda 31 kunlik oyni o'z ichiga olgan har qanday oddiy grafik
            // noto'g'ri rad etiladi.
            $grafik = $this->input('grafik', []);
            if (is_array($grafik) && count($grafik) > 1) {
                $sanalar = collect($grafik)
                    ->sortBy(fn($q, $k) => (int) $k)
                    ->pluck('sana')
                    ->filter()
                    ->values();
                for ($i = 1; $i < $sanalar->count(); $i++) {
                    $oldin = \Illuminate\Support\Carbon::parse($sanalar[$i - 1]);
                    $hozir = \Illuminate\Support\Carbon::parse($sanalar[$i]);
                    if ($oldin->diffInDays($hozir) > 31) {
                        $validator->errors()->add(
                            'grafik',
                            ($i + 1) . "-to'lov sanasi oldingisidan 31 kundan ko'proq farq qiladi. To'lovlar orasi 1 oydan oshmasligi kerak."
                        );
                        break;
                    }
                }
            }
        });
    }
}
