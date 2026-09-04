<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class ProcessBulkTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('bulk-transaction.process') === true;
    }

    public function rules(): array
    {
        return [
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'transaction_date' => ['required', 'date'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['required', 'integer', 'distinct', 'exists:members,id'],
        ];
    }

    public function after(): array
    {
        return [function ($validator) {
            if ($validator->errors()->isNotEmpty()) return;
            $date = Carbon::parse($this->input('transaction_date'));
            if ($date->month !== $this->integer('month') || $date->year !== $this->integer('year')) {
                $validator->errors()->add('transaction_date', 'Tanggal transaksi wajib berada dalam periode yang dipilih.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'member_ids.required' => 'Pilih minimal satu anggota yang akan diproses.',
            'member_ids.min' => 'Pilih minimal satu anggota yang akan diproses.',
        ];
    }
}
