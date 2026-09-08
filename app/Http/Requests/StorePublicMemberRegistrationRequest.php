<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicMemberRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', Rule::exists('branches', 'id')->where('is_active', true)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:members,email', 'unique:users,email'],
            'amount_saving' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'join_date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'branch_id.required' => 'Cabang wajib dipilih.',
            'branch_id.exists' => 'Cabang tidak aktif atau tidak valid.',
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'amount_saving.required' => 'Nominal Simpanan Manasuka wajib diisi.',
            'join_date.required' => 'Tanggal bergabung wajib diisi.',
        ];
    }
}
