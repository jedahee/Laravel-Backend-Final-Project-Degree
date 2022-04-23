<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Models\User;
use Exception;
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
    *     tags = {"User"},
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
                $request->foto_perfil->move(public_path('images'), $file_name);
                $path = "public/images/" . $file_name;
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
    #              OBETENER FOTO DE PERFIL            #
    ###################################################
    */
    /**
    * @OA\Get(
    *     path="/api/get-image",
    *     tags = {"User"},
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
    #                 AÑADIR ADVERTENCIA              #
    ###################################################
    */

    /**
    * @OA\Post(
    *     path="/api/add-warning",
    *     tags = {"User"},
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
    *     tags = {"User"},
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
    *     tags = {"User"},
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
    *     tags = {"User"},
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
    *     path="/api/edit-user/id",
    *     tags = {"User"},
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
    *     path="/api/edit-email/id",
    *     tags = {"User"},
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
