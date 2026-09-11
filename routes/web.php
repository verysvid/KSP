<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PublicMemberRegistrationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\MemberUserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\SavingTypeController;
use App\Http\Controllers\SavingTransactionController;
use App\Http\Controllers\LoanTypeController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\TopUpLoanController;
use App\Http\Controllers\LoanDisbursementController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\LoanPaymentController;
use App\Http\Controllers\LoanEarlyRepaymentController; // EARLY-REPAYMENT-ROUTES
use App\Http\Controllers\LoanOverdueController;
use App\Http\Controllers\LoanDashboardController;
use App\Http\Controllers\LoanReportController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\GeneralLedgerController;
use App\Http\Controllers\TrialBalanceController;
use App\Http\Controllers\IncomeStatementController;
use App\Http\Controllers\BalanceSheetController;
use App\Models\Member;
use App\Models\SavingTransaction;

Route::middleware('guest')->group(function () {
    Route::get('/registrasi-anggota', [PublicMemberRegistrationController::class, 'create'])
        ->name('member-registration.create');
    Route::post('/registrasi-anggota', [PublicMemberRegistrationController::class, 'store'])
        ->name('member-registration.store');
});

Route::middleware(['auth'])->group(function () {
    Route::get('loan-types', [LoanTypeController::class, 'index'])
        ->middleware('can:loan-type.view')
        ->name('loan-types.index');

    Route::get('loan-types/create', [LoanTypeController::class, 'create'])
        ->middleware('can:loan-type.create')
        ->name('loan-types.create');

    Route::post('loan-types', [LoanTypeController::class, 'store'])
        ->middleware('can:loan-type.create')
        ->name('loan-types.store');

    Route::get('loan-types/{loanType}', [LoanTypeController::class, 'show'])
        ->middleware('can:loan-type.view')
        ->name('loan-types.show');

    Route::get('loan-types/{loanType}/edit', [LoanTypeController::class, 'edit'])
        ->middleware('can:loan-type.edit')
        ->name('loan-types.edit');

    Route::put('loan-types/{loanType}', [LoanTypeController::class, 'update'])
        ->middleware('can:loan-type.edit')
        ->name('loan-types.update');

    Route::patch(
        'loan-types/{loanType}/toggle-status',
        [LoanTypeController::class, 'toggleStatus']
    )
        ->middleware('can:loan-type.edit')
        ->name('loan-types.toggle-status');

    Route::get('/general-ledger', [GeneralLedgerController::class, 'index'])
        ->middleware('can:accounting.view')
        ->name('general-ledger.index');

    Route::get('/trial-balance', [TrialBalanceController::class, 'index'])
        ->middleware('can:accounting.view')
        ->name('trial-balance.index');

    Route::get('/income-statement', [IncomeStatementController::class, 'index'])
        ->middleware('can:accounting.view')
        ->name('income-statement.index');

    Route::get('/balance-sheet', [BalanceSheetController::class, 'index'])
        ->middleware('can:accounting.view')
        ->name('balance-sheet.index');
});

Route::middleware(['auth', 'branch'])->group(function () {
    Route::get('/saving-transactions', [SavingTransactionController::class, 'index'])
        ->middleware('can:saving-transaction.view')
        ->name('saving-transactions.index');

    Route::get('/saving-transactions/create', [SavingTransactionController::class, 'create'])
        ->middleware('can:saving-transaction.create')
        ->name('saving-transactions.create');

    Route::post('/saving-transactions', [SavingTransactionController::class, 'store'])
        ->middleware('can:saving-transaction.create')
        ->name('saving-transactions.store');

    Route::get('/saving-transactions/{savingTransaction}', [SavingTransactionController::class, 'show'])
        ->middleware('can:saving-transaction.view')
        ->name('saving-transactions.show');

    Route::patch('/saving-transactions/{savingTransaction}/approve', [SavingTransactionController::class, 'approve'])
        ->middleware('can:saving-transaction.approve')
        ->name('saving-transactions.approve');

    Route::patch('/saving-transactions/{savingTransaction}/reject', [SavingTransactionController::class, 'reject'])
        ->middleware('can:saving-transaction.reject')
        ->name('saving-transactions.reject');

    Route::patch('loans-overdue/refresh', [LoanOverdueController::class, 'refresh'])
        ->name('loans.overdue.refresh');

    Route::get('loan-dashboard', [LoanDashboardController::class, 'index'])
        ->middleware('can:loan.view')
        ->name('loan-dashboard.index');

    Route::middleware('can:loan.view')
        ->prefix('loan-reports')
        ->name('loan-reports.')
        ->group(function () {
            Route::get('outstanding', [LoanReportController::class, 'outstanding'])->name('outstanding');
            Route::get('due', [LoanReportController::class, 'due'])->name('due');
            Route::get('overdue', [LoanReportController::class, 'overdue'])->name('overdue');
            Route::get('payments', [LoanReportController::class, 'payments'])->name('payments');
        });

    Route::resource('accounts', AccountController::class)
        ->middleware('can:account.view')
        ->only(['index', 'create', 'store', 'show', 'edit', 'update']);

    Route::patch('accounts/{account}/toggle-status', [AccountController::class, 'toggleStatus'])
        ->middleware('can:account.view')
        ->name('accounts.toggle-status');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/saving-types', [SavingTypeController::class, 'index'])
        ->middleware('can:saving-type.view')
        ->name('saving-types.index');

    Route::get('/saving-types/create', [SavingTypeController::class, 'create'])
        ->middleware('can:saving-type.create')
        ->name('saving-types.create');

    Route::post('/saving-types', [SavingTypeController::class, 'store'])
        ->middleware('can:saving-type.create')
        ->name('saving-types.store');

    Route::get('/saving-types/{savingType}', [SavingTypeController::class, 'show'])
        ->middleware('can:saving-type.view')
        ->name('saving-types.show');

    Route::get('/saving-types/{savingType}/edit', [SavingTypeController::class, 'edit'])
        ->middleware('can:saving-type.edit')
        ->name('saving-types.edit');

    Route::put('/saving-types/{savingType}', [SavingTypeController::class, 'update'])
        ->middleware('can:saving-type.edit')
        ->name('saving-types.update');

    Route::patch('/saving-types/{savingType}/toggle-status', [SavingTypeController::class, 'toggleStatus'])
        ->middleware('can:saving-type.edit')
        ->name('saving-types.toggle-status');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->middleware('can:audit-log.view')->name('audit-logs.index');
    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->middleware('can:audit-log.view')->name('audit-logs.show');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/branches', [BranchController::class, 'index'])->middleware('can:branch.view')->name('branches.index');
    Route::get('/branches/create', [BranchController::class, 'create'])->middleware('can:branch.create')->name('branches.create');
    Route::post('/branches', [BranchController::class, 'store'])->middleware('can:branch.create')->name('branches.store');
    Route::get('/branches/{branch}', [BranchController::class, 'show'])->middleware('can:branch.view')->name('branches.show');
    Route::get('/branches/{branch}/edit', [BranchController::class, 'edit'])->middleware('can:branch.edit')->name('branches.edit');
    Route::put('/branches/{branch}', [BranchController::class, 'update'])->middleware('can:branch.edit')->name('branches.update');
    Route::patch('/branches/{branch}/toggle-status', [BranchController::class, 'toggleStatus'])->middleware('can:branch.edit')->name('branches.toggle-status');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->middleware('can:user.view')->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->middleware('can:user.create')->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->middleware('can:user.create')->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->middleware('can:user.view')->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->middleware('can:user.edit')->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('can:user.edit')->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('can:user.delete')->name('users.destroy');
    Route::patch('/users/{user}/restore', [UserController::class, 'restore'])->middleware('can:user.restore')->name('users.restore');
    Route::get('/roles', [RolePermissionController::class, 'index'])->middleware('can:role.view')->name('roles.index');
    Route::get('/roles/{role}/edit', [RolePermissionController::class, 'edit'])->middleware('can:role.edit')->name('roles.edit');
    Route::put('/roles/{role}', [RolePermissionController::class, 'update'])->middleware('can:role.edit')->name('roles.update');
});

Route::middleware(['auth', 'branch'])->group(function () {
    Route::get('/members/{member}/add-to-user', [MemberUserController::class, 'create'])
        ->middleware('can:user.create')->name('members.user.create');
    Route::post('/members/{member}/add-to-user', [MemberUserController::class, 'store'])
        ->middleware('can:user.create')->name('members.user.store');

    Route::get('members', [MemberController::class, 'index'])->middleware('can:member.view')->name('members.index');
    Route::get('members/create', [MemberController::class, 'create'])->middleware('can:member.create')->name('members.create');
    Route::post('members', [MemberController::class, 'store'])->middleware('can:member.create')->name('members.store');
    Route::patch('members/{member}/activate', [MemberController::class, 'activate'])->middleware('can:member.edit')->name('members.activate');
    Route::get('members/{member}', [MemberController::class, 'show'])->middleware('can:member.view')->name('members.show');
    Route::get('members/{member}/edit', [MemberController::class, 'edit'])->middleware('can:member.edit')->name('members.edit');
    Route::put('members/{member}', [MemberController::class, 'update'])->middleware('can:member.edit')->name('members.update');
    Route::delete('members/{member}', [MemberController::class, 'destroy'])->middleware('can:member.delete')->name('members.destroy');

    Route::get('loans', [LoanController::class, 'index'])->middleware('can:loan.view')->name('loans.index');
    Route::get('loans/create', [LoanController::class, 'create'])->middleware('can:loan.create')->name('loans.create');
    Route::post('loans', [LoanController::class, 'store'])->middleware('can:loan.create')->name('loans.store');
    Route::get('loans/{loan}/topup', [TopUpLoanController::class, 'create'])
        ->middleware('can:loan.create')->name('loans.topup.create');
    Route::post('loans/{loan}/topup', [TopUpLoanController::class, 'store'])
        ->middleware('can:loan.create')->name('loans.topup.store');
    Route::get('loans/{loan}/topup/edit', [TopUpLoanController::class, 'edit'])
        ->middleware('can:loan.edit')->name('loans.topup.edit');
    Route::put('loans/{loan}/topup', [TopUpLoanController::class, 'update'])
        ->middleware('can:loan.edit')->name('loans.topup.update');

    // EARLY-REPAYMENT-ROUTES-BEGIN
    Route::get('loans/{loan}/early-repayment', [LoanEarlyRepaymentController::class, 'create'])
        ->middleware('can:early-repayment.create')
        ->name('loans.early-repayments.create');
    Route::post('loans/{loan}/early-repayment', [LoanEarlyRepaymentController::class, 'store'])
        ->middleware('can:early-repayment.create')
        ->name('loans.early-repayments.store');
    Route::get('loans/{loan}/early-repayments/{earlyRepayment}/proof', [LoanEarlyRepaymentController::class, 'proof'])
        ->middleware('can:early-repayment.view')
        ->name('loans.early-repayments.proof');
    Route::patch('loans/{loan}/early-repayments/{earlyRepayment}/approve', [LoanEarlyRepaymentController::class, 'approve'])
        ->middleware('can:early-repayment.approve')
        ->name('loans.early-repayments.approve');
    Route::patch('loans/{loan}/early-repayments/{earlyRepayment}/reject', [LoanEarlyRepaymentController::class, 'reject'])
        ->middleware('can:early-repayment.reject')
        ->name('loans.early-repayments.reject');
    // EARLY-REPAYMENT-ROUTES-END

    Route::get('loans/{loan}', [LoanController::class, 'show'])->middleware('can:loan.view')->name('loans.show');
    Route::get('loans/{loan}/simulation', [LoanController::class, 'simulation'])->middleware('can:loan.view')->name('loans.simulation');
    Route::get('loans/{loan}/edit', [LoanController::class, 'edit'])->middleware('can:loan.edit')->name('loans.edit');
    Route::put('loans/{loan}', [LoanController::class, 'update'])->middleware('can:loan.edit')->name('loans.update');
    Route::delete('loans/{loan}', [LoanController::class, 'destroy'])->middleware('can:loan.delete')->name('loans.destroy');
    Route::patch('loans/{loan}/submit', [LoanController::class, 'submit'])->middleware('can:loan.submit')->name('loans.submit');
    Route::patch('loans/{loan}/approve', [LoanController::class, 'approve'])->middleware('can:loan.approve')->name('loans.approve');
    Route::patch('loans/{loan}/reject', [LoanController::class, 'reject'])->middleware('can:loan.reject')->name('loans.reject');

    Route::get('loans/{loan}/disbursement', [LoanDisbursementController::class, 'create'])
        ->middleware('can:loan.disburse')->name('loans.disbursements.create');
    Route::post('loans/{loan}/disbursement', [LoanDisbursementController::class, 'store'])
        ->middleware('can:loan.disburse')->name('loans.disbursements.store');

    Route::resource('journal-entries', JournalEntryController::class)
        ->middleware('can:journal.view')->only(['index', 'show']);

    Route::get('loan-payments', [LoanPaymentController::class, 'index'])
        ->middleware('can:installment.view')->name('loan-payments.index');

    Route::get('loans/{loan}/installments/{installment}/payment', [LoanPaymentController::class, 'create'])
        ->middleware('can:loan.pay')->name('loans.installments.payments.create');

    Route::post('loans/{loan}/installments/{installment}/payment', [LoanPaymentController::class, 'store'])
        ->middleware('can:loan.pay')->name('loans.installments.payments.store');
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $totalMembers = Member::query()->count();

    $activeMembers = Member::query()
        ->where('member_status', 'ACTIVE')
        ->count();

    $totalSavings = (float) SavingTransaction::query()
        ->where('status', 'APPROVED')
        ->selectRaw('COALESCE(SUM(credit - debit), 0) as balance')
        ->value('balance');

    $pendingTransactions = SavingTransaction::query()
        ->where('status', 'PENDING')
        ->count();

    $latestTransactions = SavingTransaction::query()
        ->with(['member:id,name', 'savingType:id,name'])
        ->latest('transaction_date')
        ->latest('id')
        ->limit(5)
        ->get();

    return view('dashboard', compact(
        'totalMembers',
        'activeMembers',
        'totalSavings',
        'pendingTransactions',
        'latestTransactions'
    ));
})->middleware([
    'auth',
    'verified',
    'can:dashboard.view',
])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/branch-test', function () {
    $user = auth()->user();

    return [
        'user' => $user->name,
        'role' => $user->getRoleNames(),
        'branch_id' => $user->branch_id,
        'branch' => $user->branch?->name,
    ];
})->middleware(['auth', 'branch']);

require __DIR__.'/member-deduction-reports.php';
require __DIR__.'/member-saving-reports.php';
require __DIR__.'/member-loan-reports.php';
require __DIR__.'/bulk-transactions.php';
require __DIR__.'/auth.php';
require __DIR__.'/application-settings.php';
