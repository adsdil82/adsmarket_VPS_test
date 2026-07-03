<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

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
            'otasining_ismi'        => ['required', 'string', 'max:100'],
            'jinsi'                 => ['required', 'in:erkak,ayol'],
            'rasm'                  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'rasm_ochir'            => ['nullable', 'boolean'],
            'telefon'               => ['required', 'string', 'max:50'],
            'passport_seriya'       => ['required', 'string', 'max:10'],
            'passport_raqam'        => ['required', 'string', 'max:20'],
            'pinfl'                 => ['required', 'digits:14'],
            'passport_berilgan_joy' => ['required', 'string', 'max:300'],
            'passport_berilgan_sana' => ['nullable', 'date', 'before_or_equal:today'],
            'passport_amal_muddati'  => ['nullable', 'date'],
            'manzil'                => ['nullable', 'string'],
            'viloyat_id'            => ['nullable', 'exists:viloyatlar,id'],
            'tuman_id'              => ['nullable', 'exists:tumanlar,id'],
            'tug_sana'              => ['required', 'date', 'before:today'],
            'ish_joyi'              => ['nullable', 'string', 'max:200'],
            'lavozimi'              => ['nullable', 'string', 'max:200'],
            'oila_azolari_soni'     => ['nullable', 'integer', 'min:0', 'max:50'],
            'daromad_manbai'        => ['nullable', 'string', 'max:200'],
            'oylik_daromad'         => ['nullable', 'numeric', 'min:0'],
            'oylik_harajat'         => ['nullable', 'numeric', 'min:0'],
            'izoh'                  => ['nullable', 'string'],
            'holat'                 => ['sometimes', 'in:faol,nofaol,sudda,yomon'],
            'telefonlar'                  => ['nullable', 'array', 'max:3'],
            'telefonlar.*.telefon'        => ['required', 'string', 'max:50'],
            'telefonlar.*.egasi_ismi'     => ['nullable', 'string', 'max:150'],
            'telefonlar.*.sms_yuborilsin' => ['nullable', 'boolean'],
            'kartalar'                    => ['nullable', 'array', 'max:5'],
            'kartalar.*.karta_raqami'     => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'filial_id.required'        => 'Filial tanlanishi shart.',
            'familiya.required'         => 'Familiya kiritilishi shart.',
            'ism.required'              => 'Ism kiritilishi shart.',
            'otasining_ismi.required'   => "Otasining ismi kiritilishi shart.",
            'jinsi.required'            => 'Jinsi tanlanishi shart.',
            'telefon.required'          => 'Telefon raqami kiritilishi shart.',
            'passport_seriya.required'  => 'Passport seriyasi kiritilishi shart.',
            'passport_raqam.required'   => 'Passport raqami kiritilishi shart.',
            'passport_berilgan_joy.required' => 'Passport berilgan joyi kiritilishi shart.',
            'tug_sana.required'         => "Tug'ilgan sana kiritilishi shart.",
            'tug_sana.before'           => "Tug'ilgan sana bugundan oldin bo'lishi kerak.",
            'pinfl.required'            => 'PINFL kiritilishi shart.',
            'pinfl.digits'              => 'PINFL aynan 14 ta raqamdan iborat bo\'lishi kerak (harflar kiritib bo\'lmaydi).',
            'telefonlar.max'            => "Qo'shimcha telefon raqamlari 3 tadan oshmasligi kerak (asosiy raqam bilan jami 4 ta).",
            'telefonlar.*.telefon.required' => 'Telefon raqami bo\'sh bo\'lmasligi kerak.',
            'kartalar.max'              => 'Plastik kartalar 5 tadan oshmasligi kerak.',
        ];
    }

    /**
     * Qo'shimcha o'zaro tekshiruvlar:
     * 1) PINFL ning 2—7 xonalari (KKOOYY) tug'ilgan sana bilan mos kelishi shart.
     * 2) PINFL ning 1-raqami (3/5=erkak, 4/6=ayol) tanlangan jinsi bilan mos kelishi shart.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $pinfl   = (string) $this->input('pinfl');
            $tugSana = $this->input('tug_sana');
            $jinsi   = $this->input('jinsi');

            if (strlen($pinfl) !== 14 || !ctype_digit($pinfl)) {
                return; // bazaviy 'digits:14' qoidasi allaqachon xatoni ko'rsatadi
            }

            if ($tugSana) {
                $kun = substr($pinfl, 1, 2);
                $oy  = substr($pinfl, 3, 2);
                $yil = substr($pinfl, 5, 2);

                $sana = Carbon::parse($tugSana);
                if ($kun !== $sana->format('d') || $oy !== $sana->format('m') || $yil !== $sana->format('y')) {
                    $validator->errors()->add(
                        'pinfl',
                        "PINFL yoki tug'ilgan sana xato: PINFL bo'yicha tug'ilgan sana {$kun}.{$oy}.{$yil}, lekin kiritilgan tug'ilgan sana bunga mos kelmayapti."
                    );
                }
            }

            if ($jinsi) {
                $birinchi = substr($pinfl, 0, 1);
                $kutilganJinsi = in_array($birinchi, ['3', '5']) ? 'erkak'
                    : (in_array($birinchi, ['4', '6']) ? 'ayol' : null);

                if ($kutilganJinsi && $jinsi !== $kutilganJinsi) {
                    $validator->errors()->add(
                        'jinsi',
                        'Jinsi PINFL bilan mos kelmayapti (PINFL bo\'yicha: ' . ($kutilganJinsi === 'erkak' ? 'Erkak' : 'Ayol') . ').'
                    );
                }
            }
        });
    }
}
