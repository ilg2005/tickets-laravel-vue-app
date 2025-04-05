<?php

use App\Http\Controllers\Ticket\FollowupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Ticket\TicketController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

Route::redirect('/', '/login'); // Redirect '/' to '/login'


Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/administration', function () {
    return Inertia::render('Administration');
})->middleware(['auth', 'verified', 'role:admin'])->name('administration');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::resource('ticket', TicketController::class)
    ->middleware(['auth', 'verified']);

Route::middleware(['auth'])->group(function () {
    // Маршруты для followups
    Route::post('/ticket/followups', [FollowupController::class, 'store'])->name('ticket.followups.store');
    Route::put('/ticket/followups/{followup}', [FollowupController::class, 'update'])->name('ticket.followups.update');
    Route::delete('/ticket/followups/{followup}', [FollowupController::class, 'destroy'])->name('ticket.followups.destroy');
    
    // Маршруты для скачивания файлов
    Route::get('/ticket/files/{file_id}', [TicketController::class, 'downloadFile'])
        ->name('ticket.files.download');
    Route::get('/ticket/followups/files/{file_id}', [FollowupController::class, 'downloadFile'])
        ->name('ticket.followups.files.download');
});


require __DIR__.'/auth.php';
