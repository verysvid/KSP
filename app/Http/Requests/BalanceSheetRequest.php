<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BalanceSheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('accounting.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'branch_id' => [
                'nullable',
                'integer',
                Rule::exists('branches', 'id'),
            ],

            'as_of_date' => [
                'nullable',
                'date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'as_of_date.date' => 'Tanggal laporan tidak valid.',
        ];
    }
}
