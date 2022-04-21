<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
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
// ---------------------------

Route::post('login', [AuthController::class, 'authenticate']);
Route::post('register', [AuthController::class, 'register']);

// -- Envio de email para recuperar la contraseña --  
Route::get('forgot-password/{email}', function ($email) {
    $token = Str::random(80);

    $user = User::where('email', $email)->firstOrFail();
    
    // Guardando el token generado en el usuario correspondiente
    if ($user) {
        $user->token_password_reset = $token;
        $user->save();
    } // else -> redireccionar a página de "fallo al actualizar la cotraseña" 

    // Envio de email
    Mail::to($email)->send(new \App\Mail\PasswordReset($email, $token));
});

// -- Validación del token enviado por parámetro y el token generado -- 
Route::get('validation-token/{email}/{get_token}', function ($email, $get_token) {
    $user = User::where('email', $email)->firstOrFail();
    
    if ($user && $user->token_password_reset == $get_token) {
        return response()->json([
            'message' => 'Token validado',
        ], Response::HTTP_OK);
    }
    
    return response()->json(["msg"=>"NO"]);
    
});

//  -- Actualización de la contraseña -- 
Route::put('update-password/{email}/{token}', [AuthController::class, 'updatePassword']);


// Necesita autenticación
// -----------------------
Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('get-user', [AuthController::class, 'getUser']);
    
    // -- Editar nombre de usuario --
    Route::put('edit-user', [UserController::class, 'editUser']);

    // -- Editar correo del usuario --
    Route::put('edit-email', [UserController::class, 'editEmail']);

    // -- Obtener advertencias --
    Route::get('get-warnings', [UserController::class, 'getWarnings']);

    // -- Obtener rol del usuario --
    Route::get('get-role', [UserController::class, 'getRole']);

    // -- Borrar cuenta de usuario --
    Route::delete('delete-account', [UserController::class, 'delAccount']);
});