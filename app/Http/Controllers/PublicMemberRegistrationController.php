<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicMemberRegistrationRequest;
use App\Models\Branch;
use App\Models\Member;
use App\Models\SavingType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublicMemberRegistrationController extends Controller
{
    public function create(): View
    {
        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $savingPokok = SavingType::query()
            ->where('is_active', true)
            ->where('code', 'POKOK')
            ->first();

        $savingWajib = SavingType::query()
            ->where('is_active', true)
            ->where('code', 'WAJIB')
            ->first();

        return view(
            'member-registrations.create',
            compact(
                'branches',
                'savingPokok',
                'savingWajib'
            )
        );
    }

    public function store(
        StorePublicMemberRegistrationRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        $ktpPath = $request
            ->file('id_card_image')
            ->store('members/ktp', 'public');

        Member::create([
            'branch_id' => $data['branch_id'],
			'member_type_id' => 1,
            'nik' => $data['nik'],
            'name' => $data['name'],
            'gender' => $data['gender'],
            'birth_place' => $data['birth_place'],
            'birth_date' => $data['birth_date'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'occupation' => $data['occupation'],
            'work_unit' => $data['work_unit'],
            'amount_saving' => $data['amount_saving'],
            'join_date' => $data['join_date'],
            'id_card_image' => $ktpPath,
            'member_status' => 'NEW',
        ]);

        return redirect()
            ->route('member-registration.create')
            ->with('registration_success', true);
    }
}
