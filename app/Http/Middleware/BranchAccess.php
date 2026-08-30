<?php

namespace App\Http\Middleware;

use App\Services\BranchContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BranchAccess
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

		$user = $request->user();
		
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $branchContext = app(BranchContext::class);

        /*
        |--------------------------------------------------------------------------
        | SuperAdmin
        |--------------------------------------------------------------------------
        */

        if ($branchContext->isSuperAdmin()) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | User biasa wajib mempunyai cabang
        |--------------------------------------------------------------------------
        */

        if (!$branchContext->hasBranch()) {
            abort(403, 'User belum memiliki cabang.');
        }

        /*
        |--------------------------------------------------------------------------
        | Pastikan cabang aktif
        |--------------------------------------------------------------------------
        */

        $branch = $branchContext->getCurrentBranch();

        if (!$branch || !$branch->is_active) {
            abort(403, 'Cabang user tidak aktif.');
        }

        return $next($request);
    }
}