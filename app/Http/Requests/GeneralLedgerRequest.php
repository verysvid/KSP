<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GeneralLedgerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('accounting.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'account_id' => [
                'nullable',
                'integer',
                Rule::exists('accounts', 'id'),
            ],

            'branch_id' => [
                'nullable',
                'integer',
                Rule::exists('branches', 'id'),
            ],

            'date_from' => [
                'nullable',
                'date',
            ],

            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'date_from.date' =>
                'Tanggal awal tidak valid.',

            'date_to.date' =>
                'Tanggal akhir tidak valid.',

            'date_to.after_or_equal' =>
                'Tanggal akhir harus sama atau setelah tanggal awal.',
        ];
    }
}