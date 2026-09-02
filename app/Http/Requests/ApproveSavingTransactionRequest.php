<?php

namespace App\Http\Requests;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveSavingTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
		return $this->user()?->can('saving-transaction.approve') ?? false;
    }

    public function rules(): array
    {
        return [
            'cash_account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where(
                    fn ($query) => $query
                        ->where('type', Account::TYPE_ASSET)
                        ->where('is_cash_bank', true)
                        ->where('is_active', true)
						->where('is_postable', true)
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'cash_account_id.required' =>
                'Akun Kas/Bank wajib dipilih sebelum transaksi disetujui.',

            'cash_account_id.exists' =>
                'Akun Kas/Bank tidak valid atau tidak aktif dan dapat digunakan untuk posting.',
        ];
    }
}
