<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\MemberUserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\SavingTypeController;
use App\Http\Controllers\SavingTransactionController;
use App\Http\Controllers\LoanTypeController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanDisbursementController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\LoanPaymentController;
use App\Http\Controllers\LoanOverdueController;
use App\Http\Controllers\LoanDashboardController;
use App\Http\Controllers\LoanReportController;


Route::middleware(['auth'])->group(function () {
    Route::resource(
        'loan-types',
        LoanTypeController::class
    )->only([
        'index',
        'create',
        'store',
        'show',
        'edit',
        'update',
    ]);

    Route::patch(
        'loan-types/{loanType}/toggle-status',
        [LoanTypeController::class, 'toggleStatus']
    )->name('loan-types.toggle-status');

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
		->name('loan-dashboard.index');

	Route::prefix('loan-reports')
		->name('loan-reports.')
		->group(function () {
			Route::get(
				'outstanding',
				[LoanReportController::class, 'outstanding']
			)->name('outstanding');

			Route::get(
				'due',
				[LoanReportController::class, 'due']
			)->name('due');

			Route::get(
				'overdue',
				[LoanReportController::class, 'overdue']
			)->name('overdue');

			Route::get(
				'payments',
				[LoanReportController::class, 'payments']
			)->name('payments');
		});

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
    Route::get('/branches', [BranchController::class, 'index'])
        ->middleware('can:branch.view')
        ->name('branches.index');

    Route::get('/branches/create', [BranchController::class, 'create'])
        ->middleware('can:branch.create')
        ->name('branches.create');

    Route::post('/branches', [BranchController::class, 'store'])
        ->middleware('can:branch.create')
        ->name('branches.store');

    Route::get('/branches/{branch}', [BranchController::class, 'show'])
        ->middleware('can:branch.view')
        ->name('branches.show');

    Route::get('/branches/{branch}/edit', [BranchController::class, 'edit'])
        ->middleware('can:branch.edit')
        ->name('branches.edit');

    Route::put('/branches/{branch}', [BranchController::class, 'update'])
        ->middleware('can:branch.edit')
        ->name('branches.update');

    Route::patch('/branches/{branch}/toggle-status', [BranchController::class, 'toggleStatus'])
        ->middleware('can:branch.edit')
        ->name('branches.toggle-status');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('branches', BranchController::class);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('can:user.view')
        ->name('users.index');

    Route::get('/users/create', [UserController::class, 'create'])
        ->middleware('can:user.create')
        ->name('users.create');

    Route::post('/users', [UserController::class, 'store'])
        ->middleware('can:user.create')
        ->name('users.store');

    Route::get('/users/{user}', [UserController::class, 'show'])
        ->middleware('can:user.view')
        ->name('users.show');

    Route::get('/users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('can:user.edit')
        ->name('users.edit');

    Route::put('/users/{user}', [UserController::class, 'update'])
        ->middleware('can:user.edit')
        ->name('users.update');

    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('can:user.delete')
        ->name('users.destroy');

    Route::patch('/users/{user}/restore', [UserController::class, 'restore'])
        ->middleware('can:user.restore')
        ->name('users.restore');

    Route::get('/roles', [RolePermissionController::class, 'index'])
        ->middleware('can:role.view')
        ->name('roles.index');

    Route::get('/roles/{role}/edit', [RolePermissionController::class, 'edit'])
        ->middleware('can:role.edit')
        ->name('roles.edit');

    Route::put('/roles/{role}', [RolePermissionController::class, 'update'])
        ->middleware('can:role.edit')
        ->name('roles.update');
});

Route::middleware(['auth', 'branch'])->group(function () {

    Route::get('/members/{member}/add-to-user', [MemberUserController::class, 'create'])
        ->middleware('can:user.create')
        ->name('members.user.create');

    Route::post('/members/{member}/add-to-user', [MemberUserController::class, 'store'])
        ->middleware('can:user.create')
        ->name('members.user.store');

    Route::resource('members', MemberController::class);

	Route::resource('loans', LoanController::class)->only([
			'index',
			'create',
			'store',
			'show',
			'edit',
			'update',
			'destroy',
		]);

	Route::patch('loans/{loan}/submit', [LoanController::class, 'submit'])
		->name('loans.submit');

	Route::patch('loans/{loan}/approve', [LoanController::class, 'approve'])
		->name('loans.approve');

	Route::patch('loans/{loan}/reject', [LoanController::class, 'reject'])
		->name('loans.reject');

	Route::get('loans/{loan}/disbursement', [LoanDisbursementController::class, 'create'])
		->name('loans.disbursements.create');

	Route::post('loans/{loan}/disbursement', [LoanDisbursementController::class, 'store'])
		->name('loans.disbursements.store');

	Route::resource('journal-entries', JournalEntryController::class)->only([
			'index',
			'show',
		]);

	Route::get('loan-payments', [LoanPaymentController::class, 'index'])
		->name('loan-payments.index');

	Route::get('loans/{loan}/installments/{installment}/payment', [LoanPaymentController::class, 'create'])
		->name('loans.installments.payments.create');

	Route::post('loans/{loan}/installments/{installment}/payment', [LoanPaymentController::class, 'store'])
		->name('loans.installments.payments.store');

});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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

require __DIR__.'/auth.php';
