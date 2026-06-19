<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TulovRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hisobchi to'lov qabul qila olmaydi
        return in_array($this->user()->rol, ['admin', 'menejer', 'kassir']);
    }

    public function rules(): array
    {
        return [
            'tulov_turi_id'    => ['required', 'exists:tulov_turlari,id'],
            'summa'            => ['required', 'numeric', 'min:0.01'],
            'tolov_sana'       => ['required', 'date', 'before_or_equal:today'],
            'kvitansiya_raqam' => ['nullable', 'string', 'max:100'],
            'izoh'             => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'tulov_turi_id.required' => 'To\'lov turi tanlanishi shart.',
            'summa.required'         => 'To\'lov summasi kiritilishi shart.',
            'summa.min'              => 'To\'lov summasi 0 dan katta bo\'lishi kerak.',
            'tolov_sana.required'    => 'To\'lov sanasi kiritilishi shart.',
            'tolov_sana.before_or_equal' => 'To\'lov sanasi bugundan katta bo\'lmasligi kerak.',
        ];
    }
}
