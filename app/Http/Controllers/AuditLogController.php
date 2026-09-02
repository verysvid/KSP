<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\BranchContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct(
        protected BranchContext $branchContext
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->can('audit-log.view'),
            403
        );

        $query = AuditLog::query()
            ->with([
                'user',
                'branch',
            ])
            ->latest('id');

        $branchId = $this->branchContext
            ->getCurrentBranchId();

        if ($branchId !== null) {
            $query->where(
                'branch_id',
                $branchId
            );
        }

        if (
            $search = trim(
                (string) $request->input('search')
            )
        ) {
            $query->where(function ($q) use ($search) {
                $q->where(
                    'description',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'auditable_type',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'ip_address',
                    'like',
                    "%{$search}%"
                )
                ->orWhereHas(
                    'user',
                    function ($userQuery) use ($search) {
                        $userQuery
                            ->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'email',
                                'like',
                                "%{$search}%"
                            );
                    }
                );
            });
        }

        if ($request->filled('action')) {
            $query->where(
                'action',
                strtoupper(
                    (string) $request->input('action')
                )
            );
        }

        if ($request->filled('user_id')) {
            $query->where(
                'user_id',
                $request->integer('user_id')
            );
        }

        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->input('date_from')
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->input('date_to')
            );
        }

        $logs = $query
            ->paginate(20)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | User Filter
        |--------------------------------------------------------------------------
        */

        $usersQuery = User::query()
            ->orderBy('name');

        if ($branchId !== null) {
            $usersQuery->where(
                'branch_id',
                $branchId
            );
        }

        $users = $usersQuery->get([
            'id',
            'name',
            'email',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Action Filter
        |--------------------------------------------------------------------------
        */

        $actionsQuery = AuditLog::query();

        if ($branchId !== null) {
            $actionsQuery->where(
                'branch_id',
                $branchId
            );
        }

        $actions = $actionsQuery
            ->whereNotNull('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view(
            'audit-logs.index',
            compact(
                'logs',
                'users',
                'actions'
            )
        );
    }

    public function show(
        Request $request,
        AuditLog $auditLog
    ): View {
        abort_unless(
            $request->user()?->can('audit-log.view'),
            403
        );

        $this->ensureAuditLogAccess(
            $auditLog
        );

        $auditLog->load([
            'user',
            'branch',
        ]);

        return view(
            'audit-logs.show',
            compact('auditLog')
        );
    }

    private function ensureAuditLogAccess(
        AuditLog $auditLog
    ): void {
        if ($this->branchContext->isSuperAdmin()) {
            return;
        }

        $branchId = $this->branchContext
            ->getCurrentBranchId();

        abort_unless(
            $branchId !== null
            && (int) $auditLog->branch_id
                === (int) $branchId,
            403
        );
    }
}