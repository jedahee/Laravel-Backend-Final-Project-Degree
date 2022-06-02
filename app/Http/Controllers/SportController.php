<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Exception;
use App\Models\Sport;
use Symfony\Component\HttpFoundation\Response;

class SportController extends Controller
{
    
    /*
    ###################################################
    #                OBTENER DEPORTES                 #
    ###################################################
    */

    /**
    * @OA\Get(
    *     path="/api/get-sports",
    *     tags = {"Deporte"},
    *     summary="Obtener todos los tipos de deporte",
    *     @OA\Response(
    *         response=202,
    *         description="
    *           $sports (Object [])"
    *     ),
    * )
    */
    public function getSports(Request $request)
    {
        $sports = Sport::all();

        return response()->json([
            'sports' => $sports
        ], Response::HTTP_ACCEPTED);
    }

    /*
    ###################################################
    #           OBTENER NOMBRE DEL DEPORTE            #
    ###################################################
    */

    /**
    * @OA\Get(
    *     path="/api/get-sport/{id}",
    *     tags = {"Deporte"},
    *     summary="Obtener el nombre del deporte por el id",
    *     @OA\Response(
    *         response=202,
    *         description="
    *           $sport (Object)"
    *     ),
    * )
    */
    public function getSport(Request $request, $id)
    {

        try {
            $sport = Sport::findOrFail($id);
        } catch (Exception $e) {
            return response()->json([
                'msg' => 'Este deporte no existe'
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'sport' => $sport
        ], Response::HTTP_ACCEPTED);
    }
    
}
