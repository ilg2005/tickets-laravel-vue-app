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

Route::resource('tickets', TicketController::class)
    ->middleware(['auth', 'verified']);

Route::middleware(['auth'])->group(function () {
    // Route::post('/followups', [FollowupController::class, 'store'])->middleware('can.create.solution')->name('followups.store');
    Route::post('/followups', [FollowupController::class, 'store'])->name('followups.store');
    Route::put('/followups/{followup}', [FollowupController::class, 'update'])->name('followups.update');
    Route::delete('/followups/{followup}', [FollowupController::class, 'destroy'])->name('followups.destroy');
    
    // Маршруты для скачивания файлов
    Route::get('/tickets/files/{file_id}', [TicketController::class, 'downloadFile'])
        ->name('tickets.files.download');
    Route::get('/followups/files/{file_id}', [FollowupController::class, 'downloadFile'])
        ->name('followups.files.download');
});


require __DIR__.'/auth.php';
