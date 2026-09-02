<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLoanTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:30',
                'unique:loan_types,code',
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'interest_type' => [
                'required',
                Rule::in(['FLAT', 'EFFECTIVE']),
            ],

            'interest_rate' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],

            'min_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'max_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'gte:min_amount',
            ],

            'min_tenor' => [
                'required',
                'integer',
                'min:1',
                'max:600',
            ],

            'max_tenor' => [
                'nullable',
                'integer',
                'min:1',
                'max:600',
                'gte:min_tenor',
            ],

            'penalty_type' => [
                'required',
                Rule::in([
                    'NONE',
                    'FIXED',
                    'PERCENTAGE',
                ]),
            ],

            'penalty_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
                'required_if:penalty_type,PERCENTAGE',
            ],

            'penalty_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'required_if:penalty_type,FIXED',
            ],

			'receivable_account_id' => [
				'required',
				'integer',
				Rule::exists('accounts', 'id')->where(
					fn ($query) => $query
						->where('type', 'ASSET')
						->where('is_active', true)
						->where('is_postable', true)
				),
			],

			'interest_income_account_id' => [
				'required',
				'integer',
				Rule::exists('accounts', 'id')->where(
					fn ($query) => $query
						->where('type', 'REVENUE')
						->where('is_active', true)
						->where('is_postable', true)
				),
			],

			'penalty_income_account_id' => [
				'nullable',
				'integer',
				Rule::exists('accounts', 'id')->where(
					fn ($query) => $query
						->where('type', 'REVENUE')
						->where('is_active', true)
						->where('is_postable', true)
				),
			],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->code)),
            'is_active' => $this->boolean('is_active'),
        ]);

        if ($this->penalty_type === 'NONE') {
            $this->merge([
                'penalty_rate' => null,
                'penalty_amount' => null,
            ]);
        }

        if ($this->penalty_type === 'FIXED') {
            $this->merge([
                'penalty_rate' => null,
            ]);
        }

        if ($this->penalty_type === 'PERCENTAGE') {
            $this->merge([
                'penalty_amount' => null,
            ]);
        }
    }
}