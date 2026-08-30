<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DisburseLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('loan.disburse') === true;
    }

    public function rules(): array
    {
        return [
            'disbursement_date' => [
                'required',
                'date',
            ],

            'cash_account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')
                    ->where('is_cash_bank', true)
                    ->where('is_active', true),
            ],

            'reference_no' => [
                'nullable',
                'string',
                'max:100',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'disbursement_date.required' => 'Tanggal pencairan wajib diisi.',
            'cash_account_id.required' => 'Akun Kas/Bank wajib dipilih.',
        ];
    }
}
