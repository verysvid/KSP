<?php
namespace App\Http\Controllers;
use App\Services\LoanOverdueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class LoanOverdueController extends Controller {
    public function refresh(Request $request, LoanOverdueService $service): RedirectResponse {
        abort_unless($request->user()?->can('loan.view'),403);
        $count=$service->refreshOpenInstallments();
        return back()->with('success',"Status overdue berhasil diperbarui untuk {$count} angsuran.");
    }
}
