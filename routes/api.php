<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourtController;
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

// -- Iniciar sesión --
Route::post('login', [AuthController::class, 'authenticate']);

// -- Registro --
Route::post('register', [AuthController::class, 'register']);

// -- Obtener todas las pistas --
Route::get('get-courts', [CourtController::class, 'getCourts']);

// -- Obtener una pista por su id --
Route::get('get-court/{id}', [CourtController::class, 'getCourt']);

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

    // -- Desconectar sesion --
    Route::post('logout', [AuthController::class, 'logout']);
    
    // -- Obtener usuario --
    Route::post('get-user', [AuthController::class, 'getUser']);
    
    // -- Editar nombre de usuario --
    Route::put('edit-user', [UserController::class, 'editUser']);

    // -- Editar correo del usuario --
    Route::put('edit-email', [UserController::class, 'editEmail']);

    // -- Obtener advertencias --
    Route::get('get-warnings', [UserController::class, 'getWarnings']);

    // -- Añadir advertencia --
    Route::post('add-warning/{id}', [UserController::class, 'addWarning']);

    // -- Obtener rol del usuario --
    Route::get('get-role', [UserController::class, 'getRole']);

    // -- Borrar cuenta de usuario --
    Route::delete('delete-account', [UserController::class, 'delAccount']);
    
    // -- Actualizar foto de perfil --
    Route::post('upload-image', [UserController::class, 'uploadImage']);

    // -- Obtener foto de perfil --
    Route::get('get-image', [UserController::class, 'getImage']);

    // -- Eliminar foto de perfil --
    Route::delete('delete-image', [UserController::class, 'deleteImage']);

    // -- Añadir pista --
    Route::post('add-court', [CourtController::class, 'addCourt']);

    // -- Eliminar una pista por su id --
    Route::delete('delete-court/{id}', [CourtController::class, 'deleteCourt']);

    // -- Obtener todos los usuarios --
    Route::get('get-users', [UserController::class, 'getUsers']);
    
    // -- Obtener todos los usuarios --
    Route::put('edit-court/{id}', [CourtController::class, 'editCourt']);

    // -- Actualizar foto de la pista --
    Route::post('upload-image/{id}', [CourtController::class, 'uploadImage']);

    // -- Borrar foto de la pista --
    Route::post('delete-image/{id}', [CourtController::class, 'deleteImage']);
});