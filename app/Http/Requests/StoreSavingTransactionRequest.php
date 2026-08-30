<?php

namespace App\Http\Requests;

use App\Models\Member;
use App\Models\SavingType;
use App\Services\BranchContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSavingTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('saving-transaction.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'saving_type_id' => ['required', 'integer', 'exists:saving_types,id'],
            'transaction_date' => ['required', 'date'],
            'transaction_type' => [
                'required',
                Rule::in(['SETORAN', 'PENARIKAN']),
            ],
            'amount' => ['required', 'numeric', 'gt:0'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $member = Member::query()->find($this->integer('member_id'));
                $savingType = SavingType::query()->find($this->integer('saving_type_id'));

                if (! $member || ! $savingType) {
                    return;
                }

                if ($member->member_status !== 'ACTIVE') {
                    $validator->errors()->add(
                        'member_id',
                        'Anggota tidak aktif tidak dapat melakukan transaksi simpanan.'
                    );
                }

                if (! $savingType->is_active) {
                    $validator->errors()->add(
                        'saving_type_id',
                        'Jenis simpanan tidak aktif.'
                    );
                }

                $branchContext = app(BranchContext::class);

                if (
                    ! $branchContext->isSuperAdmin()
                    && (int) $member->branch_id !== (int) $branchContext->getCurrentBranchId()
                ) {
                    $validator->errors()->add(
                        'member_id',
                        'Anggota tidak berada pada cabang user.'
                    );
                }
            },
        ];
    }
}
