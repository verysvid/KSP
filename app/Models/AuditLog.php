<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Concerns\BelongsToBranch;

class AuditLog extends Model
{
	use BelongsToBranch;
    protected $fillable = ['user_id','branch_id','action','auditable_type','auditable_id','description','old_values','new_values','ip_address','user_agent'];
    protected $casts = ['old_values'=>'array','new_values'=>'array'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function auditable(): MorphTo { return $this->morphTo(); }
}
