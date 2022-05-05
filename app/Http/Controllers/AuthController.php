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
    *     tags = {"Autentificación"},
    *     summary="Registrar usuario en el sistema",
    *     @OA\Parameter(
    *        name="nombre",
    *        in="query",
    *        description="Nombre del usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="apellidos",
    *        in="query",
    *        description="Apellidos del usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="rol_id",
    *        in="query",
    *        description="Rol del usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="email",
    *        in="query",
    *        description="Email del usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="password",
    *        in="query",
    *        description="Contraseña del usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="
    *           Usuario creado
    *           $user (Object)"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="
    *           Error de validacción"
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
            return response()->json(['error' => $validator->messages()], Response::HTTP_BAD_REQUEST);
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
        ], Response::HTTP_ACCEPTED);
    }

    /*
    ###################################################
    #                     LOGEARSE                    #
    ###################################################
    */

    /**
    * @OA\Post(
    *     path="/api/login",
    *     tags = {"Autentificación"},
    *     summary="Logear al usuario en el sistema",
    *     @OA\Parameter(
    *        name="email",
    *        in="query",
    *        description="Correo del usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="password",
    *        in="query",
    *        description="Contraseña del usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="
    *           True
    *           $token (string)
    *           Auth::user (Loguea al usuario)"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="
    *           Error de validacción"
    *     ),
    *     @OA\Response(
    *         response=401,
    *         description="
    *           Login falló"
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="
    *           Cuenta bloqueada"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="
    *           Usuario no encontrado"
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="
    *           Error (del servidor)"
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
            return response()->json(['error' => $validator->messages()], Response::HTTP_BAD_REQUEST);
        }

        //Intentamos hacer login
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                //Credenciales incorrectas.
                return response()->json(['msg' => 'Login falló',], Response::HTTP_UNAUTHORIZED);
            }
        } catch (JWTException $e) {
            //Error chungo
            return response()->json(['msg' => 'Error',], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $user = JWTAuth::user();

        if ($user) {
            if ($user->activo == 1) {
                //Devolvemos el token
                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'user' => Auth::user()
                ], Response::HTTP_ACCEPTED);  
            }
            return response()->json(['msg' => 'Cuenta bloqueada',], Response::HTTP_FORBIDDEN);
        }
        return response()->json(['msg' => 'Usuario no encontrado',], Response::HTTP_NOT_FOUND);
    }

    /*
    ###################################################
    #                   DESCONECTAR                   #
    ###################################################
    */

    /**
    * @OA\Post(
    *     path="/api/logout",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Autentificación"},
    *     summary="Desconectar el usuario del sistema",
    *     @OA\Parameter(
    *        name="token",
    *        in="query",
    *        description="Token para validar que usuario se va a desconectar",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="
    *           Usuario desconectado"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="
    *           Error de validacción"
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="
    *           false (bool)
    *           Error"
    *     ),
    * )
    */
    public function logout(Request $request) {
        //Validamos que se nos envie el token
        $validator = Validator::make($request->only('token'),
            ['token' => 'required']
        );
        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], Response::HTTP_BAD_REQUEST);
        }
        try {

            //Si el token es valido eliminamos el token desconectando al usuario.
            JWTAuth::invalidate($request->token);

            return response()->json([
                'success' => true,
                'message' => 'Usuario desconectado'
            ], Response::HTTP_ACCEPTED);

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
    *     security={{"bearerAuth":{}}},
    *     tags = {"Autentificación"},
    *     summary="Obtener usuario y ver información de este",
    *     @OA\Parameter(
    *        name="token",
    *        in="query",
    *        description="Token para validar que usuario se va a obtener",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="
    *           $user (Object)"
    *     ),
    *     @OA\Response(
    *         response=401,
    *         description="
    *           Token invalido / token expirado"
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
            return response()->json(['message' => 'Token invalido / token expirado',], Response::HTTP_UNAUTHORIZED);

        //Devolvemos los datos del usuario si todo va bien.
        return response()->json(['user' => $user], Response::HTTP_ACCEPTED);
    }

    /*
    ###################################################
    #              ACTUALIZAR CONTRASEÑA              #
    ###################################################
    */

    /**
    * @OA\Put(
    *     path="/api/update-password/{email}/{token}",
    *     tags = {"Autentificación"},
    *     summary="Actualizar contraseña del usuario",
    *     @OA\Parameter(
    *        name="email",
    *        in="path",
    *        description="Email del usuario al que se le va a cambiar la contraseña",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="token",
    *        in="path",
    *        description="Token para validar que usuario se va a cambiar la contraseña",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="password",
    *        in="query",
    *        description="Contraseña nuevo a actualizar",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="
    *           Se ha actualizado correctamente la contraseña del usuario"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="
    *           No se ha podido actualizar la contraseña del usuario"
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

            return response()->json([
                'msg' => "Se ha actualizado correctamente la contraseña del usuario",
            ], Response::HTTP_ACCEPTED);

        } 

        return response()->json([
            'msg' => "No se ha podido actualizar la contraseña del usuario",
        ], Response::HTTP_NOT_FOUND);
    }
}
