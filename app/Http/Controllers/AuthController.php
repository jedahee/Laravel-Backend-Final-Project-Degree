<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

/**
* @OA\Info(title="API Gestión de Pistas", version="1.0")
*
* @OA\Server(url="http://localhost:8000/")
* 
*/
class AuthController extends Controller
{
    /*
    ###################################################
    #                    REGISTRO                     #
    ###################################################
    */

    /**
    * @OA\Post(
    *     path="/api/register",
    *     tags = {"Auth"},
    *     summary="Registrar usuario en el sistema",
    *     @OA\Response(
    *         response=200,
    *         description="Se ha registrado correctamente"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="No se ha podido registrar"
    *     )
    * )
    */
    public function register(Request $request) {
        //Indicamos que solo queremos recibir name, email y password de la request
        $data = $request->only('nombre', 'apellidos', 'rol_id', 'email', 'password');

        //Realizamos las validaciones
        $validator = Validator::make($data, [
            'nombre' => 'required|string|max:30',
            'apellidos' => 'required|string|max:60',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:50',
            'rol_id' => 'required|integer',
        ]);

        //Devolvemos un error si fallan las validaciones
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        //Creamos el nuevo usuario
        $user = User::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'apellidos' => $request->apellidos,
            'rutaImagen' => $request->rutaImagen,
            'rol_id' => $request->rol_id,
            'password' => bcrypt($request->password)
        ]);

        //Devolvemos la respuesta con el token del usuario
        return response()->json([
            'msg' => 'Usuario creado',
            'user' => $user,
        ], Response::HTTP_OK);
    }

    /*
    ###################################################
    #                     LOGEARSE                    #
    ###################################################
    */

    /**
    * @OA\Post(
    *     path="/api/login",
    *     tags = {"Auth"},
    *     summary="Logear al usuario en el sistema",
    *     @OA\Response(
    *         response=200,
    *         description="Se ha logeado correctamente"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="No se ha podido logear el usuario"
    *     )
    * )
    */
    public function authenticate(Request $request) {
        //Indicamos que solo queremos recibir email y password de la request
        $credentials = $request->only('email', 'password');

        //Validaciones
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Devolvemos un error de validación en caso de fallo en las verificaciones
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        //Intentamos hacer login
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                //Credenciales incorrectas.
                return response()->json(['msg' => 'Login falló',], 401);
            }
        } catch (JWTException $e) {
            //Error chungo
            return response()->json(['msg' => 'Error',], 500);
        }

        $user = JWTAuth::user();

        if ($user) {
            if ($user->activo == 1) {
                //Devolvemos el token
                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'user' => Auth::user()
                ]);  
            }
            return response()->json(['msg' => 'Cuenta bloqueada',], 400);
        }
        return response()->json(['msg' => 'Usuario no encontrado',], 400);
    }

    /*
    ###################################################
    #                   DESCONECTAR                   #
    ###################################################
    */

    /**
    * @OA\Post(
    *     path="/api/logout",
    *     tags = {"Auth"},
    *     summary="Desconectar el usuario del sistema",
    *     @OA\Response(
    *         response=200,
    *         description="Se ha desconectado correctamente"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="No se ha podido desconectar el usuario"
    *     )
    * )
    */
    public function logout(Request $request) {
        //Validamos que se nos envie el token
        $validator = Validator::make($request->only('token'),
            ['token' => 'required']
        );
        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }
        try {

            //Si el token es valido eliminamos el token desconectando al usuario.
            JWTAuth::invalidate($request->token);

            return response()->json([
                'success' => true,
                'message' => 'Usuario desconectado'
            ]);

        } catch (JWTException $exception) {

            //Error chungo
            return response()->json([
                'success' => false,
                'message' => 'Error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
    ###################################################
    #                OBTENER USUARIO                  #
    ###################################################
    */

    /**
    * @OA\Post(
    *     path="/api/get-user",
    *     tags = {"Auth"},
    *     summary="Obtener usuario y ver información de este",
    *     @OA\Response(
    *         response=200,
    *         description="Se ha obtenido el usuario correctamente"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="No se ha podido obetener la información del usuario"
    *     )
    * )
    */
    public function getUser(Request $request) {
        //Validamos que la request tenga el token
        $this->validate($request, [
            'token' => 'required'
        ]);

        //Realizamos la autentificación
        $user = JWTAuth::authenticate($request->token);

        //Si no hay usuario es que el token no es valido o que ha expirado
        if(!$user)
            return response()->json(['message' => 'Token invalido / token expirado',], 401);

        //Devolvemos los datos del usuario si todo va bien.
        return response()->json(['user' => $user], 200);
    }

    /*
    ###################################################
    #              ACTUALIZAR CONTRASEÑA              #
    ###################################################
    */

    /**
    * @OA\Put(
    *     path="/api/update-password/id/token",
    *     tags = {"Auth"},
    *     summary="Actualizar contraseña del usuario",
    *     @OA\Response(
    *         response=200,
    *     ),
    *     @OA\Response(
    *         response="400",
    *     )
    * )
    */
    public function updatePassword(Request $request, $email, $token) {
        $this->validate($request, [
            'password' => 'required'
        ]);

        $user = User::where('email', $email)->where('token_password_reset', $token)->firstOrFail();
        
        if ($user) {
            $user->update([
                'password' => bcrypt($request->password),
            ]);

            $user->token_password_reset = null;
            $user->save();

            // Redireccionar a pagina "se actualizó la contraseña correctamente"
            
            // BORRAR --
            return response()->json([
                'msg' => "Se ha actualizado correctamente la contrasñea del usuario",
            ], 200);

        } // else -> redireccionar a página de "fallo al actualizar la cotraseña" 
        
        // BORRAR --
        return response()->json([
            'msg' => "No se ha podido actualizar la contraseña del usuario",
        ], 400);
    }
}
