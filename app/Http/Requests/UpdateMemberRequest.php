<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('member.edit') === true;
    }

    public function rules(): array
    {
        $member = $this->route('member');

        return [
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'member_type_id' => ['required', 'integer', 'exists:member_types,id'],
            'nik' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('members', 'nik')->ignore($member?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'work_unit' => ['nullable', 'string', 'max:255'],
            'amount_saving' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ],
            'join_date' => ['required', 'date'],
            'member_status' => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
            'id_card_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_card_image.image' => 'File KTP harus berupa gambar.',
            'id_card_image.mimes' => 'Format KTP harus JPG, JPEG, PNG, atau WEBP.',
            'id_card_image.max' => 'Ukuran file KTP maksimal 2 MB.',
        ];
    }
}
