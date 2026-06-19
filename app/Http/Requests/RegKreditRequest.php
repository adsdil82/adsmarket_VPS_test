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
        $jami     = $this->jami_summa ?? 0;
        $oldin    = $this->boshlangich_tolov ?? 0;
        $kredit   = max(0, $jami - $oldin);
        $muddati  = $this->muddati_oy ?? 1;
        $foiz     = $this->foiz_stavka ?? 0;

        // Oylik to'lov miqdorini hisoblash
        $oylik = $muddati > 0 ? round($kredit / $muddati, 2) : 0;
        if ($foiz > 0) {
            // Foizli hisob: oddiy foiz usuli
            $oylik = round(($kredit + ($kredit * $foiz / 100)) / $muddati, 2);
        }

        $this->merge([
            'kredit_summa'        => $kredit,
            'qoldiq_qarz'         => $kredit,
            'oylik_tolov_miqdori' => $oylik,
            'tolov_qilingan'      => 0,
        ]);
    }
}
