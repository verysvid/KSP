<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'branch_id'=>['nullable','integer','exists:branches,id'],
            'member_type_id'=>['required','integer','exists:member_types,id'],
            'nik'=>['nullable','string','max:30',Rule::unique('members','nik')],
            'name'=>['required','string','max:255'],
            'gender'=>['nullable',Rule::in(['L','P'])],
            'birth_place'=>['nullable','string','max:255'],
            'birth_date'=>['nullable','date'],
            'address'=>['nullable','string'],
            'phone'=>['nullable','string','max:30'],
            'email'=>['nullable','email','max:255'],
            'occupation'=>['nullable','string','max:255'],
            'join_date'=>['required','date'],
            'member_status'=>['required',Rule::in(['ACTIVE','INACTIVE'])],
            'notes'=>['nullable','string'],
        ];
    }
}
