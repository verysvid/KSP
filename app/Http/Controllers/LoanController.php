<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectLoanRequest;
use App\Http\Requests\StoreLoanRequest;
use App\Http\Requests\UpdateLoanRequest;
use App\Models\Branch;
use App\Models\Loan;
use App\Models\LoanType;
use App\Models\Member;
use App\Services\AuditLogService;
use App\Services\BranchContext;
use App\Services\LoanCalculatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function __construct(
        protected BranchContext $branchContext,
        protected AuditLogService $auditLogService,
        protected LoanCalculatorService $loanCalculatorService
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('loan.view'), 403);

        $member = $this->resolveMemberForAnggota($request, false);
        $query = Loan::query()->with(['branch:id,code,name', 'member:id,branch_id,name', 'loanType:id,code,name']);
        $this->applyAccessScope($query, $request, $member);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('loan_no', 'like', "%{$search}%")
                    ->orWhereHas('member', fn ($m) => $m->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('loanType', fn ($t) => $t->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('loan_type_id')) $query->where('loan_type_id', $request->integer('loan_type_id'));

        $loans = $query->latest('application_date')->latest('id')->paginate(15)->withQueryString();
        $loanTypes = LoanType::query()->orderBy('name')->get(['id', 'code', 'name']);
        $branches = collect();
        if ($this->branchContext->isSuperAdmin()) {
            $branches = Branch::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);
        }

        $baseStats = Loan::query();
        $this->applyAccessScope($baseStats, $request, $member, false);
        $totalLoans = (clone $baseStats)->count();
        $draftLoans = (clone $baseStats)->where('status', Loan::STATUS_DRAFT)->count();
        $submittedLoans = (clone $baseStats)->where('status', Loan::STATUS_SUBMITTED)->count();

        return view('loans.index', compact('loans', 'loanTypes', 'branches', 'totalLoans', 'draftLoans', 'submittedLoans'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('loan.create'), 403);
        $currentMember = $this->resolveMemberForAnggota($request, false);
        $loanTypes = LoanType::query()->where('is_active', true)->orderBy('name')->get();
        $branches = collect(); $members = collect();

        if ($currentMember) {
            // Anggota: member dan cabang ditentukan server-side.
        } elseif ($this->branchContext->isSuperAdmin()) {
            $branches = Branch::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);
            if ($request->filled('branch_id')) {
                $members = Member::query()->where('branch_id', $request->integer('branch_id'))->where('member_status', 'ACTIVE')->orderBy('name')->get(['id','branch_id','name']);
            }
        } else {
            $members = Member::query()->where('branch_id', $this->branchContext->getCurrentBranchId())->where('member_status', 'ACTIVE')->orderBy('name')->get(['id','branch_id','name']);
        }

        return view('loans.create', compact('loanTypes', 'branches', 'members', 'currentMember'));
    }

    public function store(StoreLoanRequest $request): RedirectResponse
    {
        abort_unless($request->user()?->can('loan.create'), 403);
        $validated = $request->validated();
        $member = $this->resolveMemberForAnggota($request, false);

        if ($member) {
            $branchId = (int) $member->branch_id;
            $memberId = (int) $member->id;
        } else {
            $branchId = $this->resolveBranchId($validated['branch_id'] ?? null);
            $memberId = (int) $validated['member_id'];
            $this->ensureMemberBelongsToBranch($memberId, $branchId);
        }

        $loanType = LoanType::query()->whereKey($validated['loan_type_id'])->where('is_active', true)->firstOrFail();
        $this->validateAgainstLoanType($loanType, (float) $validated['principal_amount'], (int) $validated['tenor_months']);

        $loan = DB::transaction(function () use ($validated, $branchId, $memberId, $loanType, $request) {
            $loan = Loan::create([
                'branch_id'=>$branchId, 'member_id'=>$memberId, 'loan_type_id'=>$loanType->id,
                'loan_no'=>$this->generateLoanNo($branchId), 'application_date'=>$validated['application_date'],
                'principal_amount'=>$validated['principal_amount'], 'interest_type'=>$loanType->interest_type,
                'interest_rate'=>$loanType->interest_rate, 'tenor_months'=>$validated['tenor_months'],
                'due_day'=>$validated['due_day'], 'status'=>Loan::STATUS_DRAFT,
                'total_principal'=>$validated['principal_amount'], 'total_interest'=>0, 'total_installment'=>0,
                'outstanding_principal'=>$validated['principal_amount'], 'outstanding_interest'=>0,
                'notes'=>$validated['notes'] ?? null, 'created_by'=>$request->user()->id, 'updated_by'=>$request->user()->id,
            ]);
            $this->auditLogService->log('CREATE', $loan, "Membuat draft pengajuan pinjaman {$loan->loan_no}", [], $loan->fresh()->toArray());
            return $loan;
        });
        return redirect()->route('loans.show', $loan)->with('success', 'Pengajuan pinjaman berhasil dibuat sebagai Draft.');
    }

    public function show(Request $request, Loan $loan): View
    {
        abort_unless($request->user()?->can('loan.view'), 403);
        $this->ensureLoanAccessible($request, $loan);
        $loan->load(['branch','member','loanType','submittedBy','approvedBy','rejectedBy','disbursedBy','createdBy','updatedBy','disbursement.cashAccount','disbursement.journalEntry','installments','payments.installment','payments.cashAccount','payments.journalEntry']);
        $hasBlockingTopUp = \App\Services\LoanTopUpGuardService::hasBlockingTopUpForSource((int) $loan->id);

        return view('loans.show', compact('loan', 'hasBlockingTopUp'));
    }

    public function edit(Request $request, Loan $loan): View
    {
        abort_unless($request->user()?->can('loan.edit'), 403);
        $this->ensureLoanAccessible($request, $loan);
        abort_if($loan->is_topup, 422, 'Draft TopUp hanya dapat diubah melalui form TopUp.');
        abort_unless($loan->status === Loan::STATUS_DRAFT, 422, 'Hanya pinjaman berstatus Draft yang dapat diubah.');
        $currentMember = $this->resolveMemberForAnggota($request, false);
        $loanTypes = LoanType::query()->where('is_active', true)->orderBy('name')->get();
        $branches = collect();
        if ($this->branchContext->isSuperAdmin()) $branches = Branch::query()->where('is_active', true)->orderBy('name')->get(['id','code','name']);
        $members = $currentMember ? collect() : Member::query()->where('branch_id', $loan->branch_id)->where('member_status','ACTIVE')->orderBy('name')->get(['id','branch_id','name']);
        return view('loans.edit', compact('loan','loanTypes','branches','members','currentMember'));
    }

    public function update(UpdateLoanRequest $request, Loan $loan): RedirectResponse
    {
        abort_unless($request->user()?->can('loan.edit'), 403);
        $this->ensureLoanAccessible($request, $loan);
        abort_if($loan->is_topup, 422, 'Draft TopUp hanya dapat diubah melalui form TopUp.');
        abort_unless($loan->status === Loan::STATUS_DRAFT, 422, 'Hanya pinjaman berstatus Draft yang dapat diubah.');
        $validated = $request->validated();
        $member = $this->resolveMemberForAnggota($request, false);
        if ($member) { $branchId=(int)$member->branch_id; $memberId=(int)$member->id; }
        else { $branchId=$this->resolveBranchId($validated['branch_id'] ?? $loan->branch_id); $memberId=(int)$validated['member_id']; $this->ensureMemberBelongsToBranch($memberId,$branchId); }
        $loanType=LoanType::query()->whereKey($validated['loan_type_id'])->where('is_active',true)->firstOrFail();
        $this->validateAgainstLoanType($loanType,(float)$validated['principal_amount'],(int)$validated['tenor_months']);
        $old=$loan->toArray();
        $loan->update(['branch_id'=>$branchId,'member_id'=>$memberId,'loan_type_id'=>$loanType->id,'application_date'=>$validated['application_date'],'principal_amount'=>$validated['principal_amount'],'interest_type'=>$loanType->interest_type,'interest_rate'=>$loanType->interest_rate,'tenor_months'=>$validated['tenor_months'],'due_day'=>$validated['due_day'],'total_principal'=>$validated['principal_amount'],'total_interest'=>0,'total_installment'=>0,'outstanding_principal'=>$validated['principal_amount'],'outstanding_interest'=>0,'notes'=>$validated['notes']??null,'updated_by'=>$request->user()->id]);
        $this->auditLogService->log('UPDATE',$loan,"Mengubah draft pengajuan pinjaman {$loan->loan_no}",$old,$loan->fresh()->toArray());
        return redirect()->route('loans.show',$loan)->with('success','Draft pengajuan pinjaman berhasil diperbarui.');
    }

    public function destroy(Request $request, Loan $loan): RedirectResponse
    {
        abort_unless($request->user()?->can('loan.delete'),403); $this->ensureLoanAccessible($request,$loan);
        abort_unless($loan->status===Loan::STATUS_DRAFT,422,'Hanya pinjaman berstatus Draft yang dapat dihapus.');
        $old=$loan->toArray(); $no=$loan->loan_no; $this->auditLogService->log('DELETE',$loan,"Menghapus draft pengajuan pinjaman {$no}",$old,[]); $loan->delete();
        return redirect()->route('loans.index')->with('success','Draft pengajuan pinjaman berhasil dihapus.');
    }

    public function simulation(Request $request, Loan $loan): View
    {
        abort_unless($request->user()?->can('loan.view'),403); $this->ensureLoanAccessible($request,$loan);
        abort_unless($loan->status===Loan::STATUS_DRAFT,422,'Simulasi hanya tersedia untuk pengajuan berstatus Draft.');
        $loan->load(['branch','member','loanType']);
        $schedule=$this->loanCalculatorService->buildSchedule($loan,$loan->application_date->toDateString());
        $totalPrincipal=round(array_sum(array_column($schedule,'principal_amount')),2);
        $totalInterest=round(array_sum(array_column($schedule,'interest_amount')),2);
        $totalInstallment=round(array_sum(array_column($schedule,'installment_amount')),2);
        return view('loans.simulation',compact('loan','schedule','totalPrincipal','totalInterest','totalInstallment'));
    }

    public function submit(Request $request, Loan $loan): RedirectResponse
    {
        abort_unless($request->user()?->can('loan.submit'),403); $this->ensureLoanAccessible($request,$loan);
        abort_unless($loan->status===Loan::STATUS_DRAFT,422,'Hanya pinjaman berstatus Draft yang dapat diajukan.');
        // Sinkronisasi saldo pinjaman lama sebelum TopUp disubmit.
        // Setelah SUBMITTED, pembayaran pinjaman lama diblokir sampai proses TopUp selesai.
        if ($loan->is_topup) {
            $sourceLoan = Loan::query()->findOrFail($loan->topup_from_loan_id);
            abort_unless($sourceLoan->status === Loan::STATUS_ACTIVE, 422, 'Pinjaman sumber TopUp sudah tidak aktif.');
            $sourceLoan->loadMissing('loanType');

            $newPrincipal = round((float) $loan->topup_amount + (float) $sourceLoan->outstanding_principal, 2);
            $this->validateAgainstLoanType($sourceLoan->loanType, $newPrincipal, (int) $loan->tenor_months);

            $loan->update([
                'principal_amount' => $newPrincipal,
                'total_principal' => $newPrincipal,
                'outstanding_principal' => $newPrincipal,
                'old_loan_no' => $sourceLoan->loan_no,
                'notes' => app(\App\Services\LoanTopUpService::class)->buildNotes($sourceLoan),
                'updated_by' => $request->user()->id,
            ]);
        }
        $old=$loan->toArray(); $loan->update(['status'=>Loan::STATUS_SUBMITTED,'submitted_at'=>now(),'submitted_by'=>$request->user()->id,'updated_by'=>$request->user()->id]);
        $this->auditLogService->log('UPDATE',$loan,"Submit pengajuan pinjaman {$loan->loan_no}",$old,$loan->fresh()->toArray());
        return redirect()->route('loans.show',$loan)->with('success','Pengajuan pinjaman berhasil disubmit.');
    }

    public function approve(Request $request, Loan $loan): RedirectResponse
    {
        abort_unless($request->user()?->can('loan.approve'),403); $this->ensureLoanAccessible($request,$loan);
        abort_unless($loan->status===Loan::STATUS_SUBMITTED,422,'Hanya pinjaman berstatus Submitted yang dapat disetujui.');
        $old=$loan->toArray(); $loan->update(['status'=>Loan::STATUS_APPROVED,'approved_at'=>now(),'approved_by'=>$request->user()->id,'rejected_at'=>null,'rejected_by'=>null,'rejection_reason'=>null,'updated_by'=>$request->user()->id]);
        $this->auditLogService->log('APPROVE',$loan,"Menyetujui pengajuan pinjaman {$loan->loan_no}",$old,$loan->fresh()->toArray());
        return redirect()->route('loans.show',$loan)->with('success','Pengajuan pinjaman berhasil disetujui. Pinjaman siap masuk proses pencairan.');
    }

    public function reject(RejectLoanRequest $request, Loan $loan): RedirectResponse
    {
        abort_unless($request->user()?->can('loan.reject'),403); $this->ensureLoanAccessible($request,$loan);
        abort_unless($loan->status===Loan::STATUS_SUBMITTED,422,'Hanya pinjaman berstatus Submitted yang dapat ditolak.');
        $old=$loan->toArray(); $loan->update(['status'=>Loan::STATUS_REJECTED,'rejected_at'=>now(),'rejected_by'=>$request->user()->id,'rejection_reason'=>$request->validated('rejection_reason'),'approved_at'=>null,'approved_by'=>null,'updated_by'=>$request->user()->id]);
        $this->auditLogService->log('REJECT',$loan,"Menolak pengajuan pinjaman {$loan->loan_no}",$old,$loan->fresh()->toArray());
        return redirect()->route('loans.show',$loan)->with('success','Pengajuan pinjaman berhasil ditolak.');
    }

    protected function resolveMemberForAnggota(Request $request, bool $required = true): ?Member
    {
        $user=$request->user(); if (!$user?->hasRole('Anggota')) return null;
        $member=$user->member()->with('branch:id,code,name,is_active')->where('member_status','ACTIVE')->first();
        abort_unless($member,403,'User Anggota belum terhubung dengan data anggota aktif.');
        abort_unless($user->branch_id && (int)$user->branch_id===(int)$member->branch_id,403,'Cabang user tidak sesuai dengan cabang data anggota.');
        abort_unless($member->branch?->is_active,403,'Cabang anggota tidak aktif.');
        return $member;
    }

    protected function applyAccessScope($query, Request $request, ?Member $member, bool $allowSuperAdminFilter = true): void
    {
        if ($member) { $query->where('member_id',$member->id)->where('branch_id',$member->branch_id); return; }
        if ($this->branchContext->isSuperAdmin()) { if ($allowSuperAdminFilter && $request->filled('branch_id')) $query->where('branch_id',$request->integer('branch_id')); return; }
        $query->where('branch_id',$this->branchContext->getCurrentBranchId());
    }

    protected function ensureLoanAccessible(Request $request, Loan $loan): void
    {
        $member=$this->resolveMemberForAnggota($request,false);
        if ($member) { abort_unless((int)$loan->member_id===(int)$member->id && (int)$loan->branch_id===(int)$member->branch_id,403); return; }
        if ($this->branchContext->isSuperAdmin()) return;
        $branchId=$this->branchContext->getCurrentBranchId();
        abort_unless($branchId!==null && (int)$loan->branch_id===(int)$branchId,403);
    }

    protected function resolveBranchId(?int $requestedBranchId): int
    {
        if ($this->branchContext->isSuperAdmin()) { abort_if(!$requestedBranchId,422,'Cabang wajib dipilih.'); return $requestedBranchId; }
        $branchId=$this->branchContext->getCurrentBranchId(); abort_if(!$branchId,403,'User belum memiliki cabang.'); return $branchId;
    }

    protected function ensureMemberBelongsToBranch(int $memberId,int $branchId): void
    {
        $exists=Member::query()->whereKey($memberId)->where('branch_id',$branchId)->where('member_status','ACTIVE')->exists();
        abort_unless($exists,422,'Anggota tidak aktif atau tidak termasuk dalam cabang yang dipilih.');
    }

    protected function validateAgainstLoanType(LoanType $loanType,float $principalAmount,int $tenorMonths): void
    {
        if ($loanType->min_amount!==null && $principalAmount<(float)$loanType->min_amount) abort(422,'Nominal pinjaman lebih kecil dari batas minimum jenis pinjaman.');
        if ($loanType->max_amount!==null && $principalAmount>(float)$loanType->max_amount) abort(422,'Nominal pinjaman melebihi batas maksimum jenis pinjaman.');
        if ($tenorMonths<(int)$loanType->min_tenor) abort(422,'Tenor pinjaman lebih kecil dari tenor minimum jenis pinjaman.');
        if ($loanType->max_tenor!==null && $tenorMonths>(int)$loanType->max_tenor) abort(422,'Tenor pinjaman melebihi tenor maksimum jenis pinjaman.');
    }

    protected function generateLoanNo(int $branchId): string
    {
        $branch=Branch::query()->findOrFail($branchId); $prefix='LN-'.strtoupper($branch->code).'-'.now()->format('Ym');
        $last=Loan::query()->where('loan_no','like',$prefix.'-%')->latest('id')->first(); $seq=0;
        if ($last) { $parts=explode('-',$last->loan_no); $seq=(int)end($parts); }
        return sprintf('%s-%06d',$prefix,$seq+1);
    }
}
