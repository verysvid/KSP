<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanTopUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('loan.create') === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'tenor_months' => $this->filled('tenor_months') ? (int) $this->tenor_months : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'topup_amount' => ['required', 'numeric', 'min:0.01'],
            'tenor_months' => ['required', 'integer', 'min:1', 'max:600'],
        ];
    }

    public function messages(): array
    {
        return [
            'topup_amount.required' => 'Nominal TopUp wajib diisi.',
            'topup_amount.numeric' => 'Nominal TopUp harus berupa angka.',
            'topup_amount.min' => 'Nominal TopUp harus lebih besar dari 0.',
            'tenor_months.required' => 'Tenor wajib diisi.',
            'tenor_months.integer' => 'Tenor harus berupa bilangan bulat.',
            'tenor_months.min' => 'Tenor minimal 1 bulan.',
            'tenor_months.max' => 'Tenor maksimal 600 bulan.',
        ];
    }
}
