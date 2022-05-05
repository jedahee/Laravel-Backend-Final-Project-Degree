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
    *     security={{"bearerAuth":{}}},
    *     tags = {"Usuario"},
    *     summary="Actualizar la foto de perfil del usuario",
    *     @OA\Parameter(
    *        name="foto_perfil",
    *        in="query",
    *        description="Foto de perfil del usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="image"
    *        )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="
    *           Foto actulizada con éxito
    *           $path (string)"
    *     ),
    *     @OA\Response(
    *         response=422,
    *         description="
    *           Foto no válida"
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
                ], Response::HTTP_ACCEPTED);
            }
    
            return response()->json([
                'msg' => 'Foto no válida',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
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
    *     security={{"bearerAuth":{}}},
    *     tags = {"Usuario"},
    *     summary="Eliminar la foto de perfil del usuario",
    *     @OA\Response(
    *         response=200,
    *         description="Se ha eliminado la foto correctamente"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="No se ha podido eliminar la foto porque no existe"
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
            ], Response::HTTP_ACCEPTED);
        }

        return response()->json([
            'msg' => 'No se ha podido eliminar la foto porque no existe'
        ], Response::HTTP_BAD_REQUEST);
    }

    /*
    ###################################################
    #              OBETENER FOTO DE PERFIL            #
    ###################################################
    */
    /**
    * @OA\Get(
    *     path="/api/get-image",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Usuario"},
    *     summary="Obtener foto de perfil del usuario",
    *     @OA\Response(
    *         response=200,
    *         description="
    *           $path_image (string)"
    *     ),
    * )
    */
    public function getImage(Request $request) {
        
        $path_image = User::where('id', $this->user->id)->get('rutaImagen');
        
        return response()->json(
            $path_image
        , Response::HTTP_ACCEPTED);
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
    *     security={{"bearerAuth":{}}},
    *     summary="Obtener las advertencias del usuario puestas por algún Moderador / Administrador",
    *     @OA\Response(
    *         response=200,
    *         description="
    *           $adv1 (string)
    *           $adv2 (string)"
    *     ),
    * )
    */
    public function getWarnings(Request $request) {
        return response()->json([
            'adv1' => $this->user->adv1,
            'adv2' => $this->user->adv2,
        ], Response::HTTP_ACCEPTED);

    }

    /*
    ###################################################
    #                 ELIMINAR CUENTA                 #
    ###################################################
    */
    /**
    * @OA\Delete(
    *     path="/api/delete-account",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Usuario"},
    *     summary="Borrar cuenta del usuario",
    *     @OA\Response(
    *         response=200,
    *         description="
    *           Se ha eliminado la cuenta correctamente"
    *          
    *     ),
    *     @OA\Response(
    *         response=406,
    *         description="
    *           No se ha podido eliminar la cuenta"
    *     )
    * )
    */
    public function delAccount(Request $request) {
        if ($this->user->delete()) {
            return response()->json([
                'msg' => 'Se ha eliminado la cuenta correctamente'
            ], Response::HTTP_ACCEPTED);
        }

        return response()->json([
            'msg' => 'No se ha podido eliminar la cuenta'
        ], Response::HTTP_NOT_ACCEPTABLE);

    }

    /*
    ###################################################
    #                   OBETENER ROL                  #
    ###################################################
    */
    /**
    * @OA\Get(
    *     path="/api/get-role",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Usuario"},
    *     summary="Obtener el rol del usuario",
    *     @OA\Response(
    *         response=200,
    *         description="
    *           $rol_id (integer)"
    *     ),
    * )
    */
    public function getRole(Request $request) {
        return response()->json([
            'rol_id' => $this->user->rol_id,
        ], Response::HTTP_ACCEPTED);

    }

    /*
    ###################################################
    #           ACTUALIZAR NOMBRE DE USUARIO          #
    ###################################################
    */

    /**
    * @OA\Put(
    *     path="/api/edit-user",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Usuario"},
    *     summary="Actualizar nombre del usuario",
    *     @OA\Parameter(
    *        name="nombre",
    *        in="query",
    *        description="Nombre a actualizar",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="
    *           Se ha actualizado correctamente el nombre de usuario"
    *     ),
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
        ], Response::HTTP_ACCEPTED);

    } 

    /*
    ###################################################
    #                 ACTUALIZAR CORREO               #
    ###################################################
    */

    /**
    * @OA\Put(
    *     path="/api/edit-email",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Usuario"},
    *     summary="Actualizar correo electrónico del usuario",
    *     @OA\Parameter(
    *        name="email",
    *        in="query",
    *        description="Email a actualizar",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Se ha actualizado correctamente el email del usuario"
    *     ),
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
        ], Response::HTTP_ACCEPTED);

    }
}
