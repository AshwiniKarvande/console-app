<?php

use App\Http\Controllers\AmcController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

// Route::middleware(['auth', 'verified'])->group(function () {
//     Route::get('amc', function () {
//         return Inertia::render('amc');
//     })->name('amc');
// });

// Route::get('/amc', [AmcController::class, 'index']);
Route::resource('amc', AmcController::class);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
