<?php

namespace App\Http\Controllers;

use App\Exports\MemberDeductionReportExport;
use App\Models\Branch;
use App\Services\BranchContext;
use App\Services\MemberDeductionReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class MemberDeductionReportController extends Controller
{
    public function __construct(
        protected BranchContext $branchContext,
        protected MemberDeductionReportService $reportService
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorizeReport($request);
        [$month, $year] = $this->validatedPeriod($request);
        $branchId = $this->resolveBranchId($request, false);
        $branch = $branchId ? Branch::find($branchId) : null;
        $report = $branchId ? $this->reportService->generate($branchId, $month, $year) : null;

        return view('reports.member-deductions.index', [
            'month' => $month,
            'year' => $year,
            'branch' => $branch,
            'branches' => $this->availableBranches(),
            'isSuperAdmin' => $this->branchContext->isSuperAdmin(),
            'currentBranch' => $this->branchContext->getCurrentBranch(),
            'report' => $report,
        ]);
    }

    public function excel(Request $request): BinaryFileResponse
    {
        $this->authorizeReport($request);
        [$month, $year] = $this->validatedPeriod($request);
        $branchId = $this->resolveBranchId($request, true);
        $branch = Branch::findOrFail($branchId);
        $report = $this->reportService->generate($branchId, $month, $year);

        return Excel::download(
            new MemberDeductionReportExport($report, $branch, $month, $year),
            $this->filename($branch, $month, $year, 'xlsx')
        );
    }

    public function pdf(Request $request): Response
    {
        $this->authorizeReport($request);
        [$month, $year] = $this->validatedPeriod($request);
        $branchId = $this->resolveBranchId($request, true);
        $branch = Branch::findOrFail($branchId);
        $report = $this->reportService->generate($branchId, $month, $year);

        return Pdf::loadView('reports.member-deductions.pdf', compact(
            'report', 'branch', 'month', 'year'
        ))
            ->setPaper('a3', 'landscape')
            ->download($this->filename($branch, $month, $year, 'pdf'));
    }

    private function authorizeReport(Request $request): void
    {
        abort_unless(
            $request->user()?->hasAnyRole(['SuperAdmin', 'Manager', 'Pengurus'])
            && $request->user()?->can('report.member-deductions.view'),
            403
        );
    }

    private function validatedPeriod(Request $request): array
    {
        $validated = validator([
            'month' => $request->input('month', now()->month),
            'year' => $request->input('year', now()->year),
        ], [
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
        ])->validate();

        return [(int) $validated['month'], (int) $validated['year']];
    }

    private function resolveBranchId(Request $request, bool $required): ?int
    {
        if (!$this->branchContext->isSuperAdmin()) {
            $branchId = $this->branchContext->getCurrentBranchId();
            abort_unless($branchId, 403, 'User belum memiliki cabang.');
            return $branchId;
        }

        if (!$request->filled('branch_id')) {
            if ($required) {
                throw ValidationException::withMessages(['branch_id' => 'Cabang wajib dipilih.']);
            }
            return null;
        }

        $branchId = $request->integer('branch_id');
        $valid = Branch::query()->whereKey($branchId)->where('is_active', true)->exists();

        if (!$valid) {
            throw ValidationException::withMessages(['branch_id' => 'Cabang tidak aktif atau tidak valid.']);
        }

        return $branchId;
    }

    private function availableBranches()
    {
        return $this->branchContext->isSuperAdmin()
            ? Branch::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name'])
            : collect();
    }

    private function filename(Branch $branch, int $month, int $year, string $extension): string
    {
        return sprintf(
            'daftar-potongan-anggota-%s-%04d-%02d.%s',
            Str::slug($branch->code),
            $year,
            $month,
            $extension
        );
    }
}
