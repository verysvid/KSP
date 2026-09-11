<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanEarlyRepaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('early-repayment.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'payment_proof' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_proof.required' => 'Bukti pembayaran/pelunasan wajib diupload.',
            'payment_proof.image' => 'Bukti pembayaran harus berupa gambar.',
            'payment_proof.mimes' => 'Format bukti pembayaran harus JPG, JPEG, PNG, atau WEBP.',
            'payment_proof.max' => 'Ukuran bukti pembayaran maksimal 5 MB.',
        ];
    }
}
