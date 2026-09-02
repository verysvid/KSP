<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id', 'member_type_id', 'user_id', 'member_number', 'nik', 'name', 'gender',
        'birth_place', 'birth_date', 'address', 'phone', 'email', 'occupation', 'amount_saving',
        'join_date', 'member_status', 'photo', 'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'join_date' => 'date',
        'amount_saving' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Member $member) {
            if (!$member->branch_id || $member->member_number) return;
            $branch = Branch::find($member->branch_id);
            if (!$branch) return;
            $last = Member::withoutGlobalScopes()->where('branch_id', $member->branch_id)->orderByDesc('id')->first();
            $next = 1;
            if ($last && $last->member_number) {
                $next = ((int) str_replace($branch->code . '-', '', $last->member_number)) + 1;
            }
            $member->member_number = sprintf('%s-%06d', $branch->code, $next);
        });
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function memberType(): BelongsTo { return $this->belongsTo(MemberType::class); }
}
