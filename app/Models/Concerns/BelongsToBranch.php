<?php

namespace App\Models\Concerns;

use App\Services\BranchContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBranch
{
    protected static function bootBelongsToBranch(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Global Branch Scope
        |--------------------------------------------------------------------------
        */

        static::addGlobalScope('branch', function (
            Builder $builder
        ) {

            /*
             * Jika belum login, jangan lakukan filtering.
             */
            if (!auth()->check()) {
                return;
            }

            $context = app(BranchContext::class);

            /*
             * SuperAdmin dapat melihat semua cabang.
             */
            if ($context->isSuperAdmin()) {
                return;
            }

            $branchId = $context->getCurrentBranchId();

            if ($branchId !== null) {
                $builder->where(
                    $builder->getModel()->getTable() . '.branch_id',
                    $branchId
                );
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Automatically assign branch when creating
        |--------------------------------------------------------------------------
        */

        static::creating(function (Model $model) {

            if (!auth()->check()) {
                return;
            }

            $context = app(BranchContext::class);

            /*
             * SuperAdmin boleh menentukan branch secara manual.
             */
            if ($context->isSuperAdmin()) {
                return;
            }

            /*
             * User biasa otomatis mendapatkan branch miliknya.
             */
            $branchId = $context->getCurrentBranchId();

            if ($branchId !== null) {
                $model->branch_id = $branchId;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationship
    |--------------------------------------------------------------------------
    */

    public function branch(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\Branch::class
        );
    }
}