<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MijozRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->rol, ['admin', 'menejer']);
    }

    public function rules(): array
    {
        return [
            'filial_id'             => ['required', 'exists:filiallar,id'],
            'familiya'              => ['required', 'string', 'max:100'],
            'ism'                   => ['required', 'string', 'max:100'],
            'otasining_ismi'        => ['nullable', 'string', 'max:100'],
            'telefon'               => ['required', 'string', 'max:50'],
            'passport_seriya'       => ['nullable', 'string', 'max:10'],
            'passport_raqam'        => ['nullable', 'string', 'max:20'],
            'pinfl'                 => ['nullable', 'string', 'max:14', 'regex:/^\d{0,14}$/'],
            'passport_berilgan_joy' => ['nullable', 'string', 'max:300'],
            'manzil'                => ['nullable', 'string'],
            'tug_sana'              => ['nullable', 'date', 'before:today'],
            'ish_joyi'              => ['nullable', 'string', 'max:200'],
            'lavozimi'              => ['nullable', 'string', 'max:200'],
            'izoh'                  => ['nullable', 'string'],
            'holat'                 => ['sometimes', 'in:faol,nofaol'],
        ];
    }

    public function messages(): array
    {
        return [
            'filial_id.required'   => 'Filial tanlanishi shart.',
            'familiya.required'    => 'Familiya kiritilishi shart.',
            'ism.required'         => 'Ism kiritilishi shart.',
            'telefon.required'     => 'Telefon raqami kiritilishi shart.',
            'tug_sana.before'      => "Tug'ilgan sana bugundan oldin bo'lishi kerak.",
            'pinfl.regex'          => 'PINFL faqat raqamlardan iborat bo\'lishi kerak.',
        ];
    }
}
