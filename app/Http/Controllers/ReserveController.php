<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use App\Models\User;
use App\Models\Reserve;
use App\Models\Court;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class ReserveController extends Controller
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
    #                OBTENER RESERVAS                 #
    ###################################################
    */
    /**
    * @OA\Get(
    *     path="/api/get-bookings",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Reservas"},
    *     summary="Obtener todas las reservas registradas",
    *     @OA\Response(
    *         response=202,
    *         description="
    *           $bookings (Object [])"
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="
    *           Necesitas ser Administrador para realizar esta operación"
    *     )
    * )
    */
    public function getReserves(Request $request)
    {
        if ($this->user->rol_id == 1) {
            $bookings = Reserve::all();

            return response()->json([
                'bookings' => $bookings
            ], Response::HTTP_ACCEPTED);
        }

        return response()->json([
            'msg' => 'Necesitas ser Administrador para realizar esta operación'
        ], Response::HTTP_FORBIDDEN);
    }

    /*
    ###################################################
    #                 AÑADIR RESERVA                  #
    ###################################################
    */
    /**
    * @OA\Post(
    *     path="/api/add-reserve",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Reservas"},
    *     summary="Registra una nueva reserva",
    *     @OA\Parameter(
    *        name="horaInicio",
    *        in="query",
    *        description="Define a la hora que empieza la reserva",
    *        required=false,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="horaFinalizacion",
    *        in="query",
    *        description="Define a la hora que acaba la reserva",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="numLista",
    *        in="query",
    *        description="Define el número en la lista en las pitas que tengan un aforo y no un horario",
    *        required=false,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="users_id",
    *        in="query",
    *        description="ID del usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="pistas_id",
    *        in="query",
    *        description="ID de la pista",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="
    *           Reserva añadida con éxito"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="
    *           Falló validación
    *           Esta reserva debe tener un número de lista y no un horario
    *           Esta reserva debe tener un horario y no un número de lista
    *           La pista sobre la que se ha hecho la reserva no existe"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="
    *           Esta pista no está disponible"
    *     ),
    * )
    */
    public function addReserve(Request $request)
    {
        $data = $request->only('horaInicio', 'horaFinalizacion', 'numLista', 'pistas_id', 'users_id');

        $validator = Validator::make($data, [
            'horaInicio' => 'nullable|string',
            'horaFinalizacion' => 'nullable|string',
            'numLista' => 'nullable|integer',
            'pistas_id' => 'required|integer',
            'users_id' => 'required|integer',
        ]);

        if ($validator->fails())
            return response()->json(['error' => $validator->messages()], Response::HTTP_BAD_REQUEST);
        else {

            try {
                $court = Court::findOrFail($request->pistas_id);
            } catch (Exception $e) {
                return response()->json([
                    "msg" => "La pista sobre la que se ha hecho la reserva no existe"
                ], Response::HTTP_BAD_REQUEST);
            }
            
            if ($court->disponible == 1) {
                if ($court->deporte_id == "5") {
                    if ($request->numLista != null && $request->horaInicio == null && $request->horaFinalizacion == null) {
                        $reserve = Reserve::create([
                            'horaInicio' => $request->horaInicio,
                            'horaFinalizacion' => $request->horaFinalizacion,
                            'numLista' => $request->numLista,
                            'pistas_id' => $request->pistas_id,
                            'users_id' => $request->users_id,
                        ]);
        
                        return response()->json([
                            'msg' => 'Reserva añadida con éxito',
                            'reserve' => $reserve
                        ], Response::HTTP_ACCEPTED);
                    } else {
                        return response()->json([
                            'msg' => 'Esta reserva debe tener un número de lista y no un horario',
                        ], Response::HTTP_BAD_REQUEST);
                    }
                } else {
                    if ($request->numLista == null && $request->horaInicio != null && $request->horaFinalizacion != null) {
                        $reserve = Reserve::create([
                            'horaInicio' => $request->horaInicio,
                            'horaFinalizacion' => $request->horaFinalizacion,
                            'numLista' => $request->numLista,
                            'pistas_id' => $request->pistas_id,
                            'users_id' => $request->users_id,
                        ]);
        
                        return response()->json([
                            'msg' => 'Reserva añadida con éxito',
                            'reserve' => $reserve
                        ], Response::HTTP_ACCEPTED);
                    } else {
                        return response()->json([
                            'msg' => 'Esta reserva debe tener un horario y no un número de lista',
                        ], Response::HTTP_BAD_REQUEST);
                    }
                }
            } else {
                return response()->json([
                    'msg' => 'Esta pista no esta disponible',
                ], Response::HTTP_NOT_FOUND);
            }
        }
    }

    /*
    ###################################################
    #                ELIMINAR RESERVA                 #
    ###################################################
    */

    /**
    * @OA\Delete(
    *     path="/api/delete-reserve/{id}",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Reservas"},
    *     summary="Eliminar una reserva",
    *     @OA\Parameter(
    *        name="id",
    *        in="path",
    *        description="ID de la reserva",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="
    *           Se ha eliminado la reserva correctamente"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="
    *           Esta reserva no existe"
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="
    *           Necesitas ser Administrador o dueño de esta reserva para realizar esta operación"
    *     )
    * )
    */
    public function deleteReserve(Request $request, $id)
    {
        try {
            $booking = Reserve::findOrFail($id);
        } catch (Exception $e) {
            return response()->json([
                'msg' => 'Esta reserva no existe'
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($this->user->id == $booking->users_id  || $this->user->rol_id == 1) {
            $booking->delete();
            return response()->json([
                'msg' => 'Se ha eliminado la reserva correctamente'
            ], Response::HTTP_ACCEPTED);
        }
        

        return response()->json([
            'msg' => 'Necesitas ser Administrador o dueño de esta reserva para realizar esta operación'
        ], Response::HTTP_FORBIDDEN);
    }

    /*
    ###################################################
    #                 EXISTE RESERVA                  #
    ###################################################
    */
    /**
    * @OA\Get(
    *     path="/api/exists-reserve/{court_id}/{user_id}",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Reservas"},
    *     summary="Comprueba si existe una reserva",
    *     @OA\Parameter(
    *        name="court_id",
    *        in="path",
    *        description="ID de la pista",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="user_id",
    *        in="path",
    *        description="ID del usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="
    *           True"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="
    *           False"
    *     )
    * )
    */
    public function existsReserve(Request $request, $court_id, $user_id)
    {

        $booking = Reserve::where('pistas_id', $court_id)->where('users_id', $user_id)->get();
        if (count($booking) != 0) {
            return response()->json([
                'booking' => $booking,
                'exists' => true
            ], Response::HTTP_ACCEPTED); 
        }

        return response()->json([
            'exists' => false
        ], Response::HTTP_NOT_FOUND);
    }

    /*
    ###################################################
    #          OBTENER RESERVAS POR USUARIO           #
    ###################################################
    */
    /**
    * @OA\Get(
    *     path="/api/get-booking-user/{user_id}",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Reservas"},
    *     summary="Devuelve todas las reservas de un usuario",
    *     @OA\Parameter(
    *        name="users_id",
    *        in="path",
    *        description="ID del usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="
    *           $bookings (Object [])
    *           No tienes reservas"
    *     ),
    * )
    */
    public function getBookingUser(Request $request, $user_id)
    {

        $bookings = Reserve::where('users_id', $user_id)->where('users_id', $user_id)->get();
        if (count($bookings) != 0) {
            return response()->json([
                'bookings' => $bookings,
            ], Response::HTTP_ACCEPTED); 
        }

        return response()->json([
            'msg' => 'No tienes reservas',
        ], Response::HTTP_ACCEPTED);
    }   
    
}
