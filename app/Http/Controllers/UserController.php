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
    *     ),
    *     @OA\Response(
    *         response="400",
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
    *     ),
    *     @OA\Response(
    *         response="400",
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
