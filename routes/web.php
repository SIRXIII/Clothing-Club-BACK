<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SocialAuthController;

Route::get('/', function () {
    return view('welcome');
});

// Social Authentication Web Routes (for OAuth callbacks)
Route::get('/social/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
