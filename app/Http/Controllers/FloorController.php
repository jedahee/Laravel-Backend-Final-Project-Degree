<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Exception;
use App\Models\Floor;
use Symfony\Component\HttpFoundation\Response;

class FloorController extends Controller
{

    /*
    ###################################################
    #                 OBTENER SUELOS                  #
    ###################################################
    */

    /**
    * @OA\Get(
    *     path="/api/get-floors",
    *     tags = {"Suelo"},
    *     summary="Obtener todos los tipos de suelos",
    *     @OA\Response(
    *         response=202,
    *         description="
    *           $floors (Object [])"
    *     ),
    * )
    */
    public function getFloors(Request $request)
    {
        $floors = Floor::all();

        return response()->json([
            'floors' => $floors
        ], Response::HTTP_ACCEPTED);
    }

    /*
    ###################################################
    #            OBTENER NOMBRE DEL SUELO             #
    ###################################################
    */

    /**
    * @OA\Get(
    *     path="/api/get-floor/{id}",
    *     tags = {"Suelo"},
    *     summary="Obtener el nombre del suelo por el id",
    *     @OA\Response(
    *         response=202,
    *         description="
    *           $floor (Object)"
    *     ),
    * )
    */
    public function getFloor(Request $request, $id)
    {

        try {
            $floor = Floor::findOrFail($id);
        } catch (Exception $e) {
            return response()->json([
                'msg' => 'Esta suelo no existe'
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'floor' => $floor
        ], Response::HTTP_ACCEPTED);
    }
    
}
