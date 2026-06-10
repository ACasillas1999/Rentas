<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaseController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LeaseNotificationController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\MonthlyReportController;
use Illuminate\Support\Facades\Route;

// Rutas de autenticación (públicas)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas — requieren sesión iniciada
Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/calendar-events', [DashboardController::class, 'calendarEvents'])->name('dashboard.calendarEvents');
    Route::get('/payment-tenants', [PaymentController::class, 'tenantOptions'])->name('payments.tenants');

    Route::resources([
        'properties' => PropertyController::class,
        'units'      => UnitController::class,
        'tenants'    => TenantController::class,
        'leases'     => LeaseController::class,
        'payments'   => PaymentController::class,
        'users'      => UserController::class,
        'expenses'   => ExpenseController::class,
    ]);

    // Rutas de Renovación de Contrato
    Route::get('/leases/{lease}/renew', [LeaseController::class, 'renew'])->name('leases.renew');
    Route::post('/leases/{lease}/renew', [LeaseController::class, 'storeRenewal'])->name('leases.renew.store');

    // Edición masiva de pagos
    Route::get('/leases/{lease}/payments/bulk-edit', [PaymentController::class, 'bulkEdit'])->name('leases.payments.bulkEdit');
    Route::post('/leases/{lease}/payments/bulk-edit', [PaymentController::class, 'bulkUpdate'])->name('leases.payments.bulkUpdate');

    Route::get('/expenses/units/{property}', [ExpenseController::class, 'unitsByProperty'])->name('expenses.unitsByProperty');

    Route::post('/payments/{payment}/mark-paid', [PaymentController::class, 'markPaid'])->name('payments.markPaid');
    Route::post('/payments/{payment}/upload-invoice', [PaymentController::class, 'uploadInvoice'])->name('payments.uploadInvoice');
    Route::post('/payments/{payment}/upload-receipt', [PaymentController::class, 'uploadReceipt'])->name('payments.uploadReceipt');
    Route::delete('/payments/{payment}/document', [PaymentController::class, 'deleteDocument'])->name('payments.deleteDocument');

    Route::get('/reports/income', [ReportController::class, 'income'])->name('reports.income');
    Route::get('/reports/income/export', [ReportController::class, 'exportIncome'])->name('reports.income.export');
    Route::get('/reports/matrix', [ReportController::class, 'matrix'])->name('reports.matrix');

    // Reporte Mensual Automático
    Route::get('/reports/monthly',       [MonthlyReportController::class, 'index'])->name('reports.monthly.index');
    Route::post('/reports/monthly/save', [MonthlyReportController::class, 'saveConfig'])->name('reports.monthly.save');
    Route::post('/reports/monthly/send', [MonthlyReportController::class, 'sendNow'])->name('reports.monthly.send');

    // Módulo de Notificaciones
    Route::get('/notifications', [LeaseNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications', [LeaseNotificationController::class, 'store'])->name('notifications.store');
    Route::post('/notifications/run', [LeaseNotificationController::class, 'runManual'])->name('notifications.run');
    Route::delete('/notifications/{notification}', [LeaseNotificationController::class, 'destroy'])->name('notifications.destroy');

    // Descargas Seguras
    Route::get('/download', [DownloadController::class, 'download'])->name('secure.download');

    // Bitácora de Actividad (solo admin)
    Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.index');
});
