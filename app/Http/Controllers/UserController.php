<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Models\User;
use Exception;
use File;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    protected $user;

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');
        if($token != '')
            $this->user = JWTAuth::parseToken()->authenticate();
    }

    /*
    ###################################################
    #              AÑADIR FOTO DE PERFIL              #
    ###################################################
    */

    /**
    * @OA\Post(
    *     path="/api/upload-image",
    *     tags = {"Usuario"},
    *     summary="Actualizar la foto de perfil del usuario",
    *     @OA\Response(
    *         response=200,
    *         description="La foto se ha actualizado correctamente"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="No se ha podido actualizar la foto"
    *     )
    * )
    */
    public function uploadImage(Request $request) {
        $data = $request->only('foto_perfil');

        $validator = Validator::make($data, [
            'foto_perfil' => 'required|image|mimes:jpg,png,jpeg,svg|max:2048|dimensions:min_width=100,min_height=100,max_width=600,max_height=600',
        ]);

        if ($validator->fails())
            return response()->json(['error' => $validator->messages()], 400);
        else {
            if ($request->foto_perfil && $request->foto_perfil->isValid()) {
                $file_name = time() . "." . $request->foto_perfil->extension();
                $request->foto_perfil->move(public_path('images/user'), $file_name);
                $path = "public/images/user/" . $file_name;
                $this->user->rutaImagen = $path;
    
                $this->user->save();
                return response()->json([
                    'msg' => 'Foto actulizada con éxito',
                    'path' => $path
                ], 200);
            }
    
            return response()->json([
                'msg' => 'Foto no válida',
            ], 400);
        }
    }

    /*
    ###################################################
    #             ELIMINAR FOTO DE PERFIL             #
    ###################################################
    */

    /**
    * @OA\Delete(
    *     path="/api/delete-image",
    *     tags = {"Usuario"},
    *     summary="Eliminar la foto de perfil del usuario",
    *     @OA\Response(
    *         response=200,
    *         description="La foto se ha eliminado correctamente"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="No se ha podido eliminar la foto"
    *     )
    * )
    */
    public function deleteImage(Request $request) {

        $file_name = explode("/", $this->user->rutaImagen)[3];
        
        if (File::exists(public_path('images/user/'.$file_name))) {
            File::delete(public_path('images/user/'.$file_name));
            $this->user->rutaImagen = null;
            $this->user->save();

            return response()->json([
                'msg' => 'Se ha eliminado la foto correctamente'
            ], 200);
        }

        return response()->json([
            'msg' => 'No se ha podido eliminar la foto porque no existe'
        ], 400);
    }

    /*
    ###################################################
    #              OBETENER FOTO DE PERFIL            #
    ###################################################
    */
    /**
    * @OA\Get(
    *     path="/api/get-image",
    *     tags = {"Usuario"},
    *     summary="Obtener foto de perfil del usuario",
    *     @OA\Response(
    *         response=200,
    *         description="Se obtuvo la foto correctamente"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="No se pudo obtener la foto"
    *     )
    * )
    */
    public function getImage(Request $request) {
        
        $path_image = User::where('id', $this->user->id)->get('rutaImagen');
        
        return response()->json(
            $path_image
        , 200);
    }

    /*
    ###################################################
    #                OBTENER USUARIOS                 #
    ###################################################
    */

    /**
    * @OA\Get(
    *     path="/api/get-users",
    *     tags = {"Autentificación"},
    *     summary="Obtener todos los usuarios",
    *     @OA\Response(
    *         response=200,
    *         description="Se han obtenido todos los usuarios correctamente"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="Se necesita ser administrador para realizar esta operación"
    *     )
    * )
    */
    public function getUsers(Request $request) {
        if ($this->user->rol_id == 1 || $this->user->rol_id == 2) {
            $users = User::all();

            return response()->json([
                'msg' => 'Se han obtenido los usuarios correctamente',
                'users' => $users
            ], 200);
        }

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
    #                 AÑADIR ADVERTENCIA              #
    ###################################################
    */

    /**
    * @OA\Post(
    *     path="/api/add-warning",
    *     tags = {"Usuario"},
    *     summary="Añadir una advertencia a un usuario",
    *     @OA\Response(
    *         response=200,
    *         description="La advertencia se ha añadido con éxito"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="La advertencia no se ha podido añadir"
    *     )
    * )
    */
    public function addWarning(Request $request, $id) {
        if ($this->user->rol_id == 1 || $this->user->rol_id == 2) {
            $data = $request->only('adv');

            $validator = Validator::make($data, [
                'adv' => 'required|min:5|max:100|string',
            ]);

            if ($validator->fails())
                return response()->json(['error' => $validator->messages()], 400);
            
            try {
                $user = User::findOrFail($id);
            } catch (Exception $e) {
                return response()->json([
                    'msg' => 'No se encuentra el usuario'
                ], 400);        
            }

            if ($user->numAdvertencias == 0) {
                $user->adv1 = $request->adv;
                $user->numAdvertencias = 1;
                $user->save();

                return response()->json([
                    'msg' => 'Primera advertencia añadida con éxito'
                ], 200);

            } else if ($user->numAdvertencias == 1) {
                $user->adv2 = $request->adv;
                $user->numAdvertencias = 2;
                $user->activo = 0;

                $user->save();
                
                
                return response()->json([
                    'msg' => 'Segunda advertencia añadida con éxito. La cuenta ha sido bloqueada'
                ], 200);
            }

            $user->save();
        }

        return response()->json([
            'msg' => 'Esta operación solo lo puede hacer un administrador o un moderador'
        ], 400);
    }

    /*
    ###################################################
    #               OBETENER ADVERTENCIAS             #
    ###################################################
    */

    /**
    * @OA\Get(
    *     path="/api/get-warnings",
    *     tags = {"Usuario"},
    *     summary="Obtener las advertencias del usuario puestas por algún Moderador / Administrador",
    *     @OA\Response(
    *         response=200,
    *         description="Se obtuvo las advertencias correctamente"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="No se pudo obtener las advertencias"
    *     )
    * )
    */
    public function getWarnings(Request $request) {
        return response()->json([
            'adv1' => $this->user->adv1,
            'adv2' => $this->user->adv2,
        ], 200);

    }

    /*
    ###################################################
    #                 ELIMINAR CUENTA                 #
    ###################################################
    */
    /**
    * @OA\Delete(
    *     path="/api/delete-account",
    *     tags = {"Usuario"},
    *     summary="Borrar cuenta del usuario",
    *     @OA\Response(
    *         response=200,
    *         description="Se ha eliminado la cuenta correctamente"
    *          
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="No se ha podido eliminar la cuenta"
    *     )
    * )
    */
    public function delAccount(Request $request) {
        if ($this->user->delete()) {
            return response()->json([
                'msg' => 'Se ha eliminado la cuenta correctamente'
            ], 200);
        }

        return response()->json([
            'msg' => 'No se ha podido eliminar la cuenta'
        ], 200);

    }

    /*
    ###################################################
    #                   OBETENER ROL                  #
    ###################################################
    */
    /**
    * @OA\Get(
    *     path="/api/get-role",
    *     tags = {"Usuario"},
    *     summary="Obtener el rol del usuario",
    *     @OA\Response(
    *         response=200,
    *         description="Se obtuvo el rol correctamente"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="No se pudo obtener el rol correctamente"
    *     )
    * )
    */
    public function getRole(Request $request) {
        return response()->json([
            'rol_id' => $this->user->rol_id,
        ], 200);

    }

    /*
    ###################################################
    #           ACTUALIZAR NOMBRE DE USUARIO          #
    ###################################################
    */

    /**
    * @OA\Put(
    *     path="/api/edit-user",
    *     tags = {"Usuario"},
    *     summary="Actualizar nombre del usuario",
    *     @OA\Response(
    *         response=200,
    *         description="Se ha podido actualizar el nombre de usuario"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="No se ha podido actualizar el nombre de usuario"
    *     )
    * )
    */
    public function editUser(Request $request) {
        $this->validate($request, [
            'nombre' => 'required|string|max:30',
        ]);
        
        $this->user->update([
            'nombre' => $request->nombre,
        ]);
            
        return response()->json([
            'msg' => "Se ha actualizado correctamente el nombre de usuario",
        ], 200);

    } 
        


    /*
    ###################################################
    #                 ACTUALIZAR CORREO               #
    ###################################################
    */

    /**
    * @OA\Put(
    *     path="/api/edit-email",
    *     tags = {"Usuario"},
    *     summary="Actualizar correo electrónico del usuario",
    *     @OA\Response(
    *         response=200,
    *         description="Se ha podido actualizar el correo del usuario"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="No se ha podido actualizar el correo del usuario"
    *     )
    * )
    */

    public function editEmail(Request $request) {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $this->user->update([
            'email' => $request->email,
        ]);    
        
        return response()->json([
            'msg' => "Se ha actualizado correctamente el email del usuario",
        ], 200);

    }
}
