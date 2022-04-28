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
    *     tags = {"Suelo"},
    *     summary="Obtener todos los tipos de deporte",
    *     @OA\Response(
    *         response=200,
    *         description="Se han obtenido los deportes correctamente"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="No se han podido obtener los deportes"
    *     )
    * )
    */
    public function getSports(Request $request)
    {
        $sports = Sport::all();

        return response()->json([
            'sports' => $sports
        ], Response::HTTP_ACCEPTED);
    }
    
}
