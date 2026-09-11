<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectLoanEarlyRepaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('early-repayment.reject') ?? false;
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
            'rejection_reason.max' => 'Alasan penolakan maksimal 2000 karakter.',
        ];
    }
}
