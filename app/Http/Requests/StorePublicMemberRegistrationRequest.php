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
            'branch_id' => [
                'required',
                'integer',
                Rule::exists('branches', 'id')->where('is_active', true),
            ],
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:30', Rule::unique('members', 'nik')],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'birth_place' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'address' => ['required', 'string'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:members,email',
                'unique:users,email',
            ],
            'occupation' => ['required', 'string', 'max:255'],
            'work_unit' => ['required', 'string', 'max:255'],
            'amount_saving' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ],
            'join_date' => ['required', 'date'],
            'id_card_image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'agreement' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'branch_id.required' => 'Cabang wajib dipilih.',
            'branch_id.exists' => 'Cabang tidak aktif atau tidak valid.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'nik.required' => 'NIK / No. KTP wajib diisi.',
            'nik.unique' => 'NIK / No. KTP sudah terdaftar.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'birth_place.required' => 'Tempat lahir wajib diisi.',
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'address.required' => 'Alamat domisili wajib diisi.',
            'phone.required' => 'Nomor telepon / WhatsApp wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'occupation.required' => 'Pekerjaan / jabatan wajib diisi.',
            'work_unit.required' => 'Unit kerja wajib diisi.',
            'amount_saving.required' => 'Nominal Simpanan Sukarela wajib diisi.',
            'join_date.required' => 'Tanggal bergabung wajib diisi.',
            'id_card_image.required' => 'Foto KTP / kartu identitas wajib diunggah.',
            'id_card_image.image' => 'File KTP harus berupa gambar.',
            'id_card_image.mimes' => 'Format KTP harus JPG, JPEG, PNG, atau WEBP.',
            'id_card_image.max' => 'Ukuran file KTP maksimal 2 MB.',
            'agreement.accepted' => 'Anda wajib menyetujui pernyataan dan kesediaan anggota.',
        ];
    }
}
