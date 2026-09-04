<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberLoanApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Anggota') === true
            && $this->user()?->can('member-loan-application.edit') === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'loan_type_id' => $this->filled('loan_type_id')
                ? (int) $this->loan_type_id
                : null,
            'tenor_months' => $this->filled('tenor_months')
                ? (int) $this->tenor_months
                : null,
            'due_day' => $this->filled('due_day')
                ? (int) $this->due_day
                : null,
        ]);
    }

    public function rules(): array
    {
        return [
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
            'loan_type_id.required' => 'Jenis pinjaman wajib dipilih.',
            'loan_type_id.exists' => 'Jenis pinjaman tidak valid atau sudah tidak aktif.',
            'application_date.required' => 'Tanggal pengajuan wajib diisi.',
            'application_date.date' => 'Format tanggal pengajuan tidak valid.',
            'principal_amount.required' => 'Nominal pinjaman wajib diisi.',
            'principal_amount.numeric' => 'Nominal pinjaman harus berupa angka.',
            'principal_amount.min' => 'Nominal pinjaman harus lebih besar dari 0.',
            'tenor_months.required' => 'Tenor wajib diisi.',
            'tenor_months.integer' => 'Tenor harus berupa bilangan bulat.',
            'tenor_months.min' => 'Tenor minimal 1 bulan.',
            'tenor_months.max' => 'Tenor maksimal 600 bulan.',
            'due_day.required' => 'Tanggal jatuh tempo bulanan wajib diisi.',
            'due_day.between' => 'Tanggal jatuh tempo harus antara tanggal 1 sampai 28.',
            'notes.max' => 'Catatan maksimal 5.000 karakter.',
        ];
    }
}
