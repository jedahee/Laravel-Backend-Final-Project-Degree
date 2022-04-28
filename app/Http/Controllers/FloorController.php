<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Exception;
use App\Models\Floor;
use Symfony\Component\HttpFoundation\Response;

class FloorController extends Controller
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
    #                 OBTENER SUELOS                  #
    ###################################################
    */

    /**
    * @OA\Get(
    *     path="/api/get-floors",
    *     tags = {"Suelo"},
    *     summary="Obtener todos los tipos de suelos",
    *     @OA\Response(
    *         response=200,
    *         description="Se han obtenido los tipos de suelo correctamente"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="No se han podido obtener los tipos de suelo"
    *     )
    * )
    */
    public function getFloors(Request $request)
    {
        $floors = Floor::all();

        return response()->json([
            'floors' => $floors
        ], Response::HTTP_ACCEPTED);
    }
    
}
