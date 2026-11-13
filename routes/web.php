<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\InkController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (ทุกคนใช้งานได้ทันที ไม่ต้องใส่รหัส / ไม่ต้อง Login)
|--------------------------------------------------------------------------
*/

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Requisition (เบิกหมึก - พนักงานทุกคนเบิกได้ทันที)
Route::get('/transactions/stock-out', [TransactionController::class, 'createStockOut'])->name('transactions.stock_out');
Route::post('/transactions/stock-out', [TransactionController::class, 'storeStockOut'])->name('transactions.stock_out.store');

// Transaction History & Slips (ดูประวัติ & พิมพ์สลิปใบเบิก)
Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

// Inks Catalog (ดูสต็อกหมึกคงเหลือ & เครื่องพิมพ์ที่รองรับ)
Route::get('/inks', [InkController::class, 'index'])->name('inks.index');
Route::get('/api/inks/{ink}', [TransactionController::class, 'apiInkInfo'])->name('api.inks.info');

// Admin Login (เฉพาะเจ้าหน้าที่ / แอดมิน)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

/*
|--------------------------------------------------------------------------
| Admin-Only Routes (เฉพาะผู้ดูแลระบบ / เจ้าหน้าที่พัสดุ ที่เข้าสู่ระบบแล้ว)
|--------------------------------------------------------------------------
*/
Route::middleware('admin')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Inks Management (เพิ่ม, แก้ไข, ลบ รุ่นหมึก)
    Route::get('/inks/create', [InkController::class, 'create'])->name('inks.create');
    Route::post('/inks', [InkController::class, 'store'])->name('inks.store');
    Route::get('/inks/{ink}/edit', [InkController::class, 'edit'])->name('inks.edit');
    Route::put('/inks/{ink}', [InkController::class, 'update'])->name('inks.update');
    Route::delete('/inks/{ink}', [InkController::class, 'destroy'])->name('inks.destroy');

    // Stock In (รับหมึกเข้าสต็อก)
    Route::get('/transactions/stock-in', [TransactionController::class, 'createStockIn'])->name('transactions.stock_in');
    Route::post('/transactions/stock-in', [TransactionController::class, 'storeStockIn'])->name('transactions.stock_in.store');

    // Departments Management
    Route::resource('departments', DepartmentController::class)->except(['create', 'edit', 'show']);

    // Admin Users Management
    Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);
});

// View Specific Ink & Slips (placed after specific routes)
Route::get('/inks/{ink}', [InkController::class, 'show'])->name('inks.show');
Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
