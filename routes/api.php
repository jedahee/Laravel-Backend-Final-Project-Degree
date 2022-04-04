<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UpdatePassword;
use Symfony\Component\HttpFoundation\Response;

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
    $token = Str::random(80);

    $user = User::where('email', $email)->firstOrFail();
    
    if ($user) {
        $user->token_password_reset = $token;
        $user->save();
    }

    Mail::to('jedahee02@gmail.com')->send(new \App\Mail\PasswordReset($email, $token));
});

Route::get('validation-token/{email}/{get_token}', function ($email, $get_token) {
    $user = User::where('email', $email)->firstOrFail();
    if ($user && $user->token_password_reset == $get_token) {
        return response()->json([
            'message' => 'Token validados',
        ], Response::HTTP_OK);
    } else {
        return response()->json(["msg"=>"NO"]);
    }
});

// Necesita autenticación

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('get-user', [AuthController::class, 'getUser']);
});