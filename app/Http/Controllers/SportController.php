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
    *         response=200,
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
    
}
