<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'member_type_id',
        'user_id',
        'member_number',
        'nik',
        'name',
        'gender',
        'birth_place',
        'birth_date',
        'address',
        'phone',
        'email',
        'occupation',
        'work_unit',
        'amount_saving',
        'join_date',
        'member_status',
        'photo',
        'id_card_image',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'join_date' => 'date',
        'amount_saving' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Member $member) {
            if (! $member->branch_id || $member->member_number) {
                return;
            }

            $branch = Branch::find($member->branch_id);

            if (! $branch) {
                return;
            }

            $prefix = strtoupper(trim($branch->code));

            $lastMember = Member::withoutGlobalScopes()
                ->where(
                    'member_number',
                    'like',
                    $prefix . '-%'
                )
                ->orderByRaw(
                    "CAST(SUBSTRING_INDEX(member_number, '-', -1) AS UNSIGNED) DESC"
                )
                ->first();

            $next = 1;

            if ($lastMember && $lastMember->member_number) {
                $lastNumber = (int) substr(
                    $lastMember->member_number,
                    strrpos($lastMember->member_number, '-') + 1
                );

                $next = $lastNumber + 1;
            }

            do {
                $memberNumber = sprintf(
                    '%s-%06d',
                    $prefix,
                    $next
                );

                $exists = Member::withoutGlobalScopes()
                    ->where(
                        'member_number',
                        $memberNumber
                    )
                    ->exists();

                if ($exists) {
                    $next++;
                }
            } while ($exists);

            $member->member_number = $memberNumber;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function memberType(): BelongsTo
    {
        return $this->belongsTo(MemberType::class);
    }
}
