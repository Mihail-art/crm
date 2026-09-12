<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\PlaceholderPageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.signin');
})->name('home');

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/clients', fn () => app(PlaceholderPageController::class)->show('clients'))->name('clients');
    Route::get('/deals', fn () => app(PlaceholderPageController::class)->show('deals'))->name('deals');
    Route::get('/orders', fn () => app(PlaceholderPageController::class)->show('orders'))->name('orders');
    Route::get('/products', [ProductController::class, 'index'])->name('products');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
    Route::patch('/products/{product}/toggle-active', [ProductController::class, 'toggleActive'])->name('products.toggle-active');
    Route::get('/messages', fn () => app(PlaceholderPageController::class)->show('messages'))->name('messages');
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::post('/tasks/{task}/checklist', [TaskController::class, 'addChecklistItem'])->name('tasks.checklist.store');
    Route::patch('/checklist-items/{item}/toggle', [TaskController::class, 'toggleChecklistItem'])->name('tasks.checklist.toggle');
    Route::delete('/checklist-items/{item}', [TaskController::class, 'destroyChecklistItem'])->name('tasks.checklist.destroy');
    Route::post('/tasks/{task}/comments', [TaskController::class, 'addComment'])->name('tasks.comments.store');
    Route::post('/tasks/{task}/attachments', [TaskController::class, 'addAttachment'])->name('tasks.attachments.store');
    Route::delete('/attachments/{attachment}', [TaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy');
    Route::get('/analytics', fn () => app(PlaceholderPageController::class)->show('analytics'))->name('analytics');
    Route::get('/finance', fn () => app(PlaceholderPageController::class)->show('finance'))->name('finance');
    Route::get('/warehouse', fn () => app(PlaceholderPageController::class)->show('warehouse'))->name('warehouse');
    Route::get('/team', [TeamController::class, 'index'])->name('team');
    Route::post('/team', [TeamController::class, 'store'])->name('team.store');
    Route::put('/team/{user}', [TeamController::class, 'update'])->name('team.update');
    Route::delete('/team/{user}', [TeamController::class, 'destroy'])->name('team.destroy');
    Route::patch('/team/{user}/toggle-active', [TeamController::class, 'toggleActive'])->name('team.toggle-active');
    Route::post('/team/{user}/reset-password', [TeamController::class, 'resetPassword'])->name('team.reset-password');
    Route::get('/settings', fn () => app(PlaceholderPageController::class)->show('settings'))->name('settings');
});
