<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    public function log(string $action, Model $model, string $description, array $oldValues = [], array $newValues = []): AuditLog
    {
        $user = auth()->user();
        $branchId = $user?->branch_id;
        if ($model->getAttribute('branch_id')) $branchId = $model->getAttribute('branch_id');
        return AuditLog::create([
            'user_id'=>$user?->id,
            'branch_id'=>$branchId,
            'action'=>strtoupper($action),
            'auditable_type'=>$model::class,
            'auditable_id'=>$model->getKey(),
            'description'=>$description,
            'old_values'=>$oldValues ?: null,
            'new_values'=>$newValues ?: null,
            'ip_address'=>request()?->ip(),
            'user_agent'=>request()?->userAgent(),
        ]);
    }
}
