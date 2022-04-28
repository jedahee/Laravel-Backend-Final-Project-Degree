<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use App\Models\User;
use App\Models\Court;
use Exception;
use File;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class CourtController extends Controller
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
    #                 OBTENER PISTAS                  #
    ###################################################
    */

    /**
    * @OA\Get(
    *     path="/api/get-courts",
    *     tags = {"Pista"},
    *     summary="Obtener todas las pistas registradas",
    *     @OA\Response(
    *         response=200,
    *         description="Se han obtenido las pistas correctamente"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="No se han podido obtener las pistas"
    *     )
    * )
    */
    public function getCourts(Request $request)
    {
        $courts = Court::all();

        return response()->json([
            'courts' => $courts
        ], 200);
    }

    /*
    ###################################################
    #                 ELIMINAR PISTA                  #
    ###################################################
    */

    /**
    * @OA\Delete(
    *     path="/api/delete-court",
    *     tags = {"Pista"},
    *     summary="Eliminar una pista por su id",
    *     @OA\Response(
    *         response=200,
    *         description="Se ha eliminado la pista correctamente"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="No se ha podido eliminar la pista"
    *     )
    * )
    */
    public function deleteCourt(Request $request, $id)
    {
        if ($this->user->rol_id == 1) {
            try {
                $court = Court::findOrFail($id);
            } catch (Exception $e) {
                return response()->json([
                    'msg' => 'Esta pista no existe'
                ], 400);
            }

            $court->delete();
            return response()->json([
                'msg' => 'Se ha eliminado la pista correctamente'
            ], 200);
        }
        

        return response()->json([
            'msg' => 'Necesitas ser Administrador para realizar esta operación'
        ], 400);
    }

    /*
    ###################################################
    #                  OBTENER PISTA                  #
    ###################################################
    */

    /**
    * @OA\Get(
    *     path="/api/get-court/id",
    *     tags = {"Pista"},
    *     summary="Obtener una pista por su id",
    *     @OA\Response(
    *         response=200,
    *         description="Se han obtenido la pista correctamente"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="No se ha podido obtener la pista, el id no es correcto"
    *     )
    * )
    */
    public function getCourt(Request $request, $id)
    {
        try {
            $court = Court::findOrFail($id);
            return response()->json([
                'court' => $court
            ], 200);    
        
        } catch (Exception $e) {
            return response()->json([
                'msg' => 'Esta pista no existe'
            ], 400);
        }
    }

    /*
    ###################################################
    #                  EDITAR PISTA                   #
    ###################################################
    */

    /**
    * @OA\Put(
    *     path="/api/edit-court",
    *     tags = {"Pista"},
    *     summary="Actualizar una pista",
    *     @OA\Response(
    *         response=200,
    *         description="La pista se ha actualizado correctamente"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="No se ha podido actualizar la pista"
    *     )
    * )
    */
    public function editCourt(Request $request, $id) {

        if ($this->user->rol_id == 1) {
            
            try {
                $court = Court::findOrFail($id);
            } catch (Exception $e) {
                return response()->json([
                    'msg' => 'Esta pista no existe'
                ], 400);
            }
        
            $data = $request->only('nombre', 'horaInicio', 'horaFinalizacion', 'direccion', 'aforo', 'precioPorHora', 'disponible', 'campoAbierto', 'iluminacion', 'suelo_id', 'deporte_id');

            $validator = Validator::make($data, [
                'nombre' => 'required|string|min:0|max:50',
                'horaInicio' => 'nullable|string',
                'horaFinalizacion' => 'nullable|string',
                'direccion' => 'required|string|min:0|max:100',
                'aforo' => 'nullable|integer',
                'precioPorHora' => 'required|numeric',
                'disponible' => 'required|integer|min:0|max:1',
                'campoAbierto' => 'required|integer|min:0|max:1',
                'iluminacion' => 'required|integer|min:0|max:1',
                'suelo_id' => 'required|integer',
                'deporte_id' => 'required|integer',
                
            ]);

            if ($validator->fails())
                return response()->json(['error' => $validator->messages()], 400);
            else {
                    if ($request->deporte_id == "5") {
                        if ($request->aforo != null && $request->horaInicio == null && $request->horaFinalizacion == null) {
                            $court->update([
                                'nombre' => $request->nombre,
                                'horaInicio' => $request->horaInicio,
                                'horaFinalizacion' => $request->horaFinalizaicon,
                                'direccion' => $request->direccion,
                                'aforo' => $request->aforo,
                                'precioPorHora' => $request->precioPorHora,
                                'disponible' => $request->disponible,
                                'campoAbierto' => $request->campoAbierto,
                                'iluminacion' => $request->iluminacion,
                                'suelo_id' => $request->suelo_id,
                                'deporte_id' => $request->deporte_id
                            ]);

                            return response()->json([
                                'msg' => 'Pista actualizada con éxito',
                                'court' => $court
                            ], 200);
                        } else {
                            return response()->json([
                                'msg' => 'El rocódromo debe tener aforo y no un horario',
                            ], 400);
                        }
                    
                    } else {
                        if ($request->aforo == null && $request->horaInicio != null && $request->horaFinalizacion != null) {
                            $court->update([
                                'nombre' => $request->nombre,
                                'horaInicio' => $request->horaInicio,
                                'horaFinalizacion' => $request->horaFinalizaicon,
                                'direccion' => $request->direccion,
                                'aforo' => $request->aforo,
                                'precioPorHora' => $request->precioPorHora,
                                'disponible' => $request->disponible,
                                'campoAbierto' => $request->campoAbierto,
                                'iluminacion' => $request->iluminacion,
                                'suelo_id' => $request->suelo_id,
                                'deporte_id' => $request->deporte_id
                            ]);

                            return response()->json([
                                'msg' => 'Pista actualizada con éxito',
                                'court' => $court
                            ], 200);
                        } else {
                            return response()->json([
                                'msg' => 'Esta pista debe tener un horario y no aforo',
                            ], 400);
                        }
                    }
        
            }
        }
        return response()->json([
            'msg' => 'Para hacer esta operación debes ser Administrador',
        ], 400);
    }

    /*
    ###################################################
    #                  AÑADIR PISTA                   #
    ###################################################
    */

    /**
    * @OA\Post(
    *     path="/api/add-court",
    *     tags = {"Pista"},
    *     summary="Añadir una pista nueva",
    *     @OA\Response(
    *         response=200,
    *         description="La pista se ha añadido correctamente"
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="No se ha podido añadir la pista"
    *     )
    * )
    */
    public function addCourt(Request $request) {

        if ($this->user->rol_id == 1) {
            $data = $request->only('nombre', 'horaInicio', 'horaFinalizacion', 'direccion', 'aforo', 'precioPorHora', 'disponible', 'campoAbierto', 'iluminacion', 'suelo_id', 'deporte_id');

            $validator = Validator::make($data, [
                'nombre' => 'required|string|min:0|max:50',
                'horaInicio' => 'nullable|string',
                'horaFinalizacion' => 'nullable|string',
                'direccion' => 'required|string|min:0|max:100',
                'aforo' => 'nullable|integer',
                'precioPorHora' => 'required|numeric',
                'disponible' => 'required|integer|min:0|max:1',
                'campoAbierto' => 'required|integer|min:0|max:1',
                'iluminacion' => 'required|integer|min:0|max:1',
                'suelo_id' => 'required|integer',
                'deporte_id' => 'required|integer',
                
            ]);

            if ($validator->fails())
                return response()->json(['error' => $validator->messages()], 400);
            else {
                    if ($request->deporte_id == "5") {
                        if ($request->aforo != null && $request->horaInicio == null && $request->horaFinalizacion == null) {
                            $court = Court::create([
                                'nombre' => $request->nombre,
                                'horaInicio' => $request->horaInicio,
                                'rutaImagen' => public_path("images/default.jpg"),
                                'horaFinalizacion' => $request->horaFinalizaicon,
                                'direccion' => $request->direccion,
                                'aforo' => $request->aforo,
                                'precioPorHora' => $request->precioPorHora,
                                'disponible' => $request->disponible,
                                'campoAbierto' => $request->campoAbierto,
                                'iluminacion' => $request->iluminacion,
                                'suelo_id' => $request->suelo_id,
                                'deporte_id' => $request->deporte_id
                            ]);

                            return response()->json([
                                'msg' => 'Pista añadida con éxito',
                                'court' => $court
                            ], 200);
                        } else {
                            return response()->json([
                                'msg' => 'El rocódromo debe tener aforo y no un horario',
                            ], 400);
                        }
                    
                    } else {
                        if ($request->aforo == null && $request->horaInicio != null && $request->horaFinalizacion != null) {
                            $court = Court::create([
                                'nombre' => $request->nombre,
                                'horaInicio' => $request->horaInicio,
                                'rutaImagen' => public_path("images/default.jpg"),
                                'horaFinalizacion' => $request->horaFinalizacion,
                                'direccion' => $request->direccion,
                                'aforo' => $request->aforo,
                                'precioPorHora' => $request->precioPorHora,
                                'disponible' => $request->disponible,
                                'campoAbierto' => $request->campoAbierto,
                                'iluminacion' => $request->iluminacion,
                                'suelo_id' => $request->suelo_id,
                                'deporte_id' => $request->deporte_id
                            ]);

                            return response()->json([
                                'msg' => 'Pista añadida con éxito',
                                'court' => $court
                            ], 200);
                        } else {
                            return response()->json([
                                'msg' => 'Esta pista debe tener un horario y no aforo',
                            ], 400);
                        }
                    }
        
            }
        }
        return response()->json([
            'msg' => 'Para hacer esta operación debes ser Administrador',
        ], 400);
    }

    /*
    ###################################################
    #                   AÑADIR FOTO                   #
    ###################################################
    */

    /**
    * @OA\Post(
    *     path="/api/upload-image",
    *     tags = {"Pista"},
    *     summary="Actualizar la foto de la pista",
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
    public function uploadImage(Request $request, $id) {
        if ($this->user->rol_id == 1) {
            try {
                $court = Court::findOrFail($id);
            } catch (Exception $e) {
                return response()->json([
                    'msg' => 'Esta pista no existe'
                ], 400);
            }

            $data = $request->only('foto');

            $validator = Validator::make($data, [
                'foto' => 'required|image|mimes:jpg,png,jpeg,svg|max:2048|dimensions:min_width=350,min_height=350,max_width=1980,max_height=1980',
            ]);

            if ($validator->fails())
                return response()->json(['error' => $validator->messages()], 400);
            else {
                if ($request->foto && $request->foto->isValid()) {
                    $file_name = time() . "." . $request->foto->extension();
                    $request->foto->move(public_path('images/court'), $file_name);
                    $path = "public/images/court/" . $file_name;
                    $court->rutaImagen = $path;
        
                    $court->save();
                    return response()->json([
                        'msg' => 'Foto actulizada con éxito',
                        'path' => $path
                    ], 200);
                }
        
                return response()->json([
                    'msg' => 'Foto no válida',
                ], 400);
            }
        } else {
            return response()->json([
                'msg' => 'Necesitas ser Administrador para realizar esta operación'
            ], 400);
        }
    }

    /*
    ###################################################
    #                  ELIMINAR FOTO                  #
    ###################################################
    */

    /**
    * @OA\Post(
    *     path="/api/delete-image",
    *     tags = {"Pista"},
    *     summary="Eliminar foto de uan pista",
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
    public function deleteImage(Request $request, $id) {
        if ($this->user->rol_id == 1) {
            try {
                $court = Court::findOrFail($id);
            } catch (Exception $e) {
                return response()->json([
                    'msg' => 'Esta pista no existe'
                ], 400);
            }

            $file_name = explode("/", $court->rutaImagen)[3];
        
            if (File::exists(public_path('images/court/'.$file_name))) {
                File::delete(public_path('images/court/'.$file_name));
                $court->rutaImagen = public_path('images/default.jpg');
                $court->save();

                return response()->json([
                    'msg' => 'Se ha eliminado la foto correctamente'
                ], 200);
            }

            return response()->json([
                'msg' => 'No se ha podido eliminar la foto porque no existe'
            ], 400);
        } else {
            return response()->json([
                'msg' => 'Necesitas ser Administrador para realizar esta operación'
            ], 400);
        }
    }
}
