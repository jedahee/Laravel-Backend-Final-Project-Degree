<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UpdatePassword;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


//  NO necesita autenticación

Route::post('login', [AuthController::class, 'authenticate']);
Route::post('register', [AuthController::class, 'register']);

// Envio de email para recuperar la contraseña  
Route::get('forgot-password/{email}', function ($email) {
    Mail::to('jedahee02@gmail.com')->send(new \App\Mail\PasswordReset($email, $token));
});

Route::get('validation-token/{get_token}', function ($get_token) {
    // Arreglar
        Route::post('update-password', [UpdatePassword::class, 'test']);
    
});

// Necesita autenticación

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('get-user', [AuthController::class, 'getUser']);
});