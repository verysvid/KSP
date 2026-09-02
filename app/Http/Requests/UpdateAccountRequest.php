<?php

namespace App\Http\Requests;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('account.edit') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_cash_bank' => $this->boolean('is_cash_bank'),
            'is_postable' => $this->boolean('is_postable'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $account = $this->route('account');
        return [
            'code' => ['required','string','max:30',Rule::unique('accounts','code')->ignore($account?->id)],
            'name' => ['required','string','max:150'],
            'type' => ['required', Rule::in(['ASSET','LIABILITY','EQUITY','REVENUE','EXPENSE'])],
            'parent_id' => ['nullable','integer',Rule::exists('accounts','id')],
            'normal_balance' => ['required',Rule::in(['DEBIT','CREDIT'])],
            'sort_order' => ['nullable','integer','min:0','max:999999'],
            'description' => ['nullable','string','max:1000'],
            'is_cash_bank' => ['required','boolean'],
            'is_postable' => ['required','boolean'],
            'is_active' => ['required','boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $account = $this->route('account');
            $parentId = $this->integer('parent_id');
            if ($account && $parentId && $parentId === (int) $account->id) {
                $validator->errors()->add('parent_id', 'Akun tidak dapat menjadi parent untuk dirinya sendiri.');
                return;
            }
            if ($account && !$this->boolean('is_postable') && $account->journalLines()->exists()) {
                $validator->errors()->add('is_postable', 'Akun yang sudah pernah dipakai pada jurnal tidak dapat diubah menjadi non-postable.');
            }
            if (!$parentId) return;
            $parent = Account::find($parentId);
            if (!$parent) return;
            if ($parent->type !== $this->input('type')) {
                $validator->errors()->add('parent_id', 'Parent akun harus memiliki tipe akun yang sama.');
            }
            if ($parent->is_postable) {
                $validator->errors()->add('parent_id', 'Parent akun harus berupa akun header/non-postable.');
            }
        });
    }
}
