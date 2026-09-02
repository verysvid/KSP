<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberLoanReportController extends Controller
{
    /**
     * Laporan anggota hanya menampilkan pinjaman yang sudah menjadi pinjaman aktual.
     * DRAFT / SUBMITTED / APPROVED / REJECTED / CANCELLED tetap berada di modul transaksi.
     */
    private const REPORT_STATUSES = [
        Loan::STATUS_ACTIVE,
        Loan::STATUS_PAID_OFF,
    ];

    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless(
            $user && $user->hasAnyRole(['Anggota', 'Manager', 'Pengurus', 'SuperAdmin']),
            403
        );

        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'status' => ['nullable', 'in:ACTIVE,PAID_OFF'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $branches = collect();
        $members = collect();
        $selectedBranchId = null;
        $selectedMember = null;

        $query = Loan::query()
            ->with([
                'branch:id,code,name',
                'member:id,branch_id,member_number,name',
                'loanType:id,code,name',
            ])
            ->whereIn('status', self::REPORT_STATUSES);

        if ($user->hasRole('Anggota')) {
            $selectedMember = $user->member()
                ->with('branch:id,code,name')
                ->first();

            abort_unless(
                $selectedMember,
                403,
                'User Anggota belum terhubung dengan data anggota.'
            );

            $selectedBranchId = (int) $selectedMember->branch_id;
            $query->where('member_id', $selectedMember->id);
        } elseif ($user->hasRole('Manager', 'Pengurus')) {
            abort_unless(
                $user->branch_id,
                403,
                'User Manager belum memiliki cabang.'
            );

            $selectedBranchId = (int) $user->branch_id;
            $query->where('branch_id', $selectedBranchId);

            $members = Member::query()
                ->where('branch_id', $selectedBranchId)
                ->where('member_status', 'ACTIVE')
                ->orderBy('name')
                ->get(['id', 'branch_id', 'member_number', 'name']);

            if (!empty($validated['member_id'])) {
                $selectedMember = Member::query()
                    ->with('branch:id,code,name')
                    ->whereKey((int) $validated['member_id'])
                    ->where('branch_id', $selectedBranchId)
                    ->first();

                abort_unless($selectedMember, 403);
                $query->where('member_id', $selectedMember->id);
            }
        } else {
            // SuperAdmin
            $branches = Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name']);

            if (!empty($validated['branch_id'])) {
                $selectedBranchId = (int) $validated['branch_id'];
                $query->where('branch_id', $selectedBranchId);

                $members = Member::query()
                    ->where('branch_id', $selectedBranchId)
                    ->where('member_status', 'ACTIVE')
                    ->orderBy('name')
                    ->get(['id', 'branch_id', 'member_number', 'name']);
            }

            if (!empty($validated['member_id'])) {
                abort_unless(
                    $selectedBranchId,
                    422,
                    'Pilih cabang terlebih dahulu sebelum memilih anggota.'
                );

                $selectedMember = Member::query()
                    ->with('branch:id,code,name')
                    ->whereKey((int) $validated['member_id'])
                    ->where('branch_id', $selectedBranchId)
                    ->first();

                abort_unless($selectedMember, 403);
                $query->where('member_id', $selectedMember->id);
            }
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['search'])) {
            $search = trim((string) $validated['search']);

            $query->where(function ($q) use ($search) {
                $q->where('loan_no', 'like', "%{$search}%")
                    ->orWhereHas('member', function ($memberQuery) use ($search) {
                        $memberQuery->where('member_number', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('loanType', function ($typeQuery) use ($search) {
                        $typeQuery->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        // Statistik mengikuti seluruh filter yang sedang aktif.
        $totalLoans = (clone $query)->count();
        $activeLoans = (clone $query)
            ->where('status', Loan::STATUS_ACTIVE)
            ->count();
        $paidOffLoans = (clone $query)
            ->where('status', Loan::STATUS_PAID_OFF)
            ->count();
        $outstandingPrincipal = (float) (clone $query)
            ->sum('outstanding_principal');

        $loans = $query
            ->latest('disbursed_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('reports.loans.index', compact(
            'branches',
            'members',
            'selectedBranchId',
            'selectedMember',
            'loans',
            'totalLoans',
            'activeLoans',
            'paidOffLoans',
            'outstandingPrincipal'
        ));
    }

    public function show(Request $request, Loan $loan): View
    {
        $user = $request->user();

        abort_unless(
            $user && $user->hasAnyRole(['Anggota', 'Manager', 'Pengurus', 'SuperAdmin']),
            403
        );

        // Jangan biarkan halaman laporan dipakai untuk membaca draft/proses pengajuan.
        abort_unless(
            in_array($loan->status, self::REPORT_STATUSES, true),
            404
        );

        if ($user->hasRole('Anggota')) {
            $member = $user->member()->first();

            abort_unless(
                $member && (int) $loan->member_id === (int) $member->id,
                403
            );
        } elseif ($user->hasRole('Manager', 'Pengurus')) {
            abort_unless(
                $user->branch_id
                && (int) $loan->branch_id === (int) $user->branch_id,
                403
            );
        }

        $loan->load([
            'branch',
            'member',
            'loanType',
            'disbursement.cashAccount',
            'installments',
            'payments.installment',
            'payments.cashAccount',
        ]);

        $paidPrincipal = (float) $loan->payments->sum('principal_amount');
        $paidInterest = (float) $loan->payments->sum('interest_amount');
        $paidPenalty = (float) $loan->payments->sum('penalty_amount');
        $totalPaid = (float) $loan->payments->sum('total_amount');
        $paidInstallments = $loan->installments
            ->where('status', 'PAID')
            ->count();

        return view('reports.loans.show', compact(
            'loan',
            'paidPrincipal',
            'paidInterest',
            'paidPenalty',
            'totalPaid',
            'paidInstallments'
        ));
    }
}
