<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('audit-log.view'), 403);
        $currentUser = $request->user();
        $query = AuditLog::query()->with(['user','branch'])->latest('id');
        if (! $currentUser->hasRole('SuperAdmin')) $query->where('branch_id', $currentUser->branch_id);
        if ($search = trim((string)$request->input('search'))) {
            $query->where(function($q) use ($search) {
                $q->where('description','like',"%{$search}%")
                  ->orWhere('auditable_type','like',"%{$search}%")
                  ->orWhere('ip_address','like',"%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name','like',"%{$search}%")->orWhere('email','like',"%{$search}%");
                  });
            });
        }
        if ($request->filled('action')) $query->where('action', strtoupper($request->input('action')));
        if ($request->filled('user_id')) $query->where('user_id', $request->integer('user_id'));
        if ($request->filled('date_from')) $query->whereDate('created_at','>=',$request->input('date_from'));
        if ($request->filled('date_to')) $query->whereDate('created_at','<=',$request->input('date_to'));
        $logs = $query->paginate(20)->withQueryString();
        $usersQuery = User::query()->orderBy('name');
        if (! $currentUser->hasRole('SuperAdmin')) $usersQuery->where('branch_id',$currentUser->branch_id);
        $users = $usersQuery->get(['id','name','email']);
        $actions = AuditLog::query()
            ->when(! $currentUser->hasRole('SuperAdmin'), fn($q)=>$q->where('branch_id',$currentUser->branch_id))
            ->whereNotNull('action')->distinct()->orderBy('action')->pluck('action');
        return view('audit-logs.index', compact('logs','users','actions'));
    }

    public function show(Request $request, AuditLog $auditLog): View
    {
        abort_unless($request->user()?->can('audit-log.view'), 403);
        if (! $request->user()->hasRole('SuperAdmin') && (int)$auditLog->branch_id !== (int)$request->user()->branch_id) abort(403);
        $auditLog->load(['user','branch']);
        return view('audit-logs.show', compact('auditLog'));
    }
}
