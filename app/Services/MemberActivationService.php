<?php

namespace App\Services;

use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MemberActivationService
{
    public function activate(Member $member): User
    {
        return DB::transaction(function () use ($member) {
            $member->refresh();

            if ($member->user_id) {
                throw ValidationException::withMessages([
                    'member' => 'Anggota ini sudah memiliki akun user.',
                ]);
            }

            if (! $member->email) {
                throw ValidationException::withMessages([
                    'email' => 'Email anggota wajib diisi sebelum aktivasi.',
                ]);
            }

            if (User::query()->where('email', $member->email)->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'Email anggota sudah digunakan oleh akun user lain.',
                ]);
            }

            $user = User::create([
                'branch_id' => $member->branch_id,
                'name' => $member->name,
                'email' => $member->email,
                'password' => 'password123',
                'is_active' => true,
            ]);

            $user->assignRole('Anggota');

            $member->update([
                'user_id' => $user->id,
                'member_status' => 'ACTIVE',
            ]);

            return $user;
        });
    }
}
