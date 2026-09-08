<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicMemberRegistrationRequest;
use App\Models\Branch;
use App\Models\Member;
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

        return view(
            'member-registrations.create',
            compact('branches')
        );
    }

    public function store(
        StorePublicMemberRegistrationRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        Member::create([
            'branch_id' => $data['branch_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'amount_saving' => $data['amount_saving'],
            'join_date' => $data['join_date'],
            'member_status' => 'NEW',
        ]);

        return redirect()
            ->route('member-registration.create')
            ->with('registration_success', true);
    }
}
