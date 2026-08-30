<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('loan.edit') === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'branch_id' => $this->filled('branch_id') ? (int) $this->branch_id : null,
            'member_id' => $this->filled('member_id') ? (int) $this->member_id : null,
            'loan_type_id' => $this->filled('loan_type_id') ? (int) $this->loan_type_id : null,
            'tenor_months' => $this->filled('tenor_months') ? (int) $this->tenor_months : null,
            'due_day' => $this->filled('due_day') ? (int) $this->due_day : null,
        ]);
    }

    public function rules(): array
    {
        $isSuperAdmin = $this->user()?->hasRole('SuperAdmin') === true;

        return [
            'branch_id' => [
                Rule::requiredIf($isSuperAdmin),
                'nullable',
                'integer',
                Rule::exists('branches', 'id')->where('is_active', true),
            ],

            'member_id' => [
                'required',
                'integer',
                Rule::exists('members', 'id'),
            ],

            'loan_type_id' => [
                'required',
                'integer',
                Rule::exists('loan_types', 'id')->where('is_active', true),
            ],

            'application_date' => [
                'required',
                'date',
            ],

            'principal_amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'tenor_months' => [
                'required',
                'integer',
                'min:1',
                'max:600',
            ],

            'due_day' => [
                'required',
                'integer',
                'between:1,28',
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
            'branch_id.required' => 'Cabang wajib dipilih.',
            'member_id.required' => 'Anggota wajib dipilih.',
            'loan_type_id.required' => 'Jenis pinjaman wajib dipilih.',
            'application_date.required' => 'Tanggal pengajuan wajib diisi.',
            'principal_amount.required' => 'Nominal pinjaman wajib diisi.',
            'principal_amount.min' => 'Nominal pinjaman harus lebih besar dari 0.',
            'tenor_months.required' => 'Tenor wajib diisi.',
            'tenor_months.min' => 'Tenor minimal 1 bulan.',
            'due_day.required' => 'Tanggal jatuh tempo bulanan wajib diisi.',
            'due_day.between' => 'Tanggal jatuh tempo harus antara tanggal 1 sampai 28.',
        ];
    }
}
