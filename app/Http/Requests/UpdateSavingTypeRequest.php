<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Account;

class UpdateSavingTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('saving-type.edit') ?? false;
    }

    public function rules(): array
    {
        $savingType = $this->route('savingType');

        return [
            'code' => [
                'required',
                'string',
                'max:30',
                'alpha_dash',
                Rule::unique('saving_types', 'code')->ignore($savingType),
            ],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric', 'min:0'],

			'liability_account_id' => [
				'required',
				'integer',
				Rule::exists('accounts', 'id')->where(
					fn ($query) => $query
						->where('type', Account::TYPE_LIABILITY)
						->where('is_active', true)
						->where('is_postable', true)
				),
			],
			
            'is_mandatory' => ['required', 'boolean'],
            'is_withdrawable' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_mandatory' => $this->boolean('is_mandatory'),
            'is_withdrawable' => $this->boolean('is_withdrawable'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->boolean('is_mandatory') && $this->boolean('is_withdrawable')) {
                $validator->errors()->add(
                    'is_withdrawable',
                    'Jenis simpanan wajib tidak boleh dapat ditarik.'
                );
            }
        });
    }
}
