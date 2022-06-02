<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use App\Models\User;
use App\Models\Court;
use App\Models\Comment;
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
    *         response=202,
    *         description="
    *           $courts (Object [])"
    *     ),
    * )
    */
    public function getCourts(Request $request)
    {
        $courts = Court::all();

        return response()->json([
            'courts' => $courts
        ], Response::HTTP_ACCEPTED);
    }

    /*
    ###################################################
    #            OBTENER LIKES AND DISLIKES           #
    ###################################################
    */
    /**
    * @OA\Get(
    *     path="/api/get-likes-and-dislikes/{id}",
    *     tags = {"Pista"},
    *     summary="Obtener todos los likes y dislikes de una pista",
    *     @OA\Parameter(
    *        name="id",
    *        in="path",
    *        description="ID de la pista",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="
    *           Likes: X, Dislikes: X"
    *     ),
    * )
    */
    public function getLikesAndDislikes(Request $request, $id)
    {
        $likes = Comment::where('pistas_id', $id)->where('like', 1)->count('like');
        $dislikes = Comment::where('pistas_id', $id)->where('like', 0)->count('like');
        
        return response()->json([
            'likes' => $likes,
            'dislikes' => $dislikes
        ], Response::HTTP_ACCEPTED);
    }

    /**
    * @OA\Delete(
    *     path="/api/delete-court/{id}",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Pista"},
    *     summary="Eliminar una pista por su id",
    *     @OA\Parameter(
    *        name="id",
    *        in="path",
    *        description="ID de la pista",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="
    *           Se ha eliminado la pista correctamente"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="Esta pista no existe"
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
                ], Response::HTTP_NOT_FOUND);
            }

            $court->delete();
            return response()->json([
                'msg' => 'Se ha eliminado la pista correctamente'
            ], Response::HTTP_ACCEPTED);
        }
        

        return response()->json([
            'msg' => 'Necesitas ser Administrador para realizar esta operación'
        ], Response::HTTP_FORBIDDEN);
    }

    /*
    ###################################################
    #                  OBTENER PISTA                  #
    ###################################################
    */

    /**
    * @OA\Get(
    *     path="/api/get-court/{id}",
    *     tags = {"Pista"},
    *     summary="Obtener una pista por su id",
    *     @OA\Parameter(
    *        name="id",
    *        in="path",
    *        description="ID de la pista",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="
    *           $court (Object)"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="
    *           Esta pista no existe"
    *     )
    * )
    */
    public function getCourt(Request $request, $id)
    {
        try {
            $court = Court::findOrFail($id);
            return response()->json([
                'court' => $court
            ], Response::HTTP_ACCEPTED);    
        
        } catch (Exception $e) {
            return response()->json([
                'msg' => 'Esta pista no existe'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /*
    ###################################################
    #                  EDITAR PISTA                   #
    ###################################################
    */

    /**
    * @OA\Put(
    *     path="/api/edit-court/{id}",
    *     tags = {"Pista"},
    *     security={{"bearerAuth":{}}},
    *     summary="Actualizar una pista",
    *     @OA\Parameter(
    *        name="id",
    *        in="path",
    *        description="ID de la pista",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="nombre",
    *        in="query",
    *        description="Nombre de la pista a editar",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="horaFinalizacion",
    *        in="query",
    *        description="A partir de esta hora se pueden hacer citas",
    *        required=false,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="direccion",
    *        in="query",
    *        description="Dirección de la pista",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="aforo",
    *        in="query",
    *        description="Capacidad máxima de personas en una pista",
    *        required=false,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="precioPorHora",
    *        in="query",
    *        description="Precio de la pista por hora",
    *        required=true,
    *        @OA\Schema(
    *            type="numeric"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="disponible",
    *        in="query",
    *        description="Especifica si la pista esta disponible para reservar",
    *        required=true,
    *        @OA\Schema(
    *            type="tinyInt"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="campoAbierto",
    *        in="query",
    *        description="Especifica si el campo es al aire libre o es en un sitio cerrado",
    *        required=true,
    *        @OA\Schema(
    *            type="tinyInt"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="iluminacion",
    *        in="query",
    *        description="Especifica si la pista tiene iluminación de calidad",
    *        required=true,
    *        @OA\Schema(
    *            type="tinyInt"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="suelo_id",
    *        in="query",
    *        description="ID del suelo",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="deporte_id",
    *        in="query",
    *        description="ID del deporte",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="
    *           Pista actualizada con éxito"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="
    *           Error de validación
    *           El rocódromo debe tener aforo y no un horario
    *           Esta pista debe tener un horario y no aforo"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="
    *           Esta pista no existe"
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
                ], Response::HTTP_NOT_FOUND);
            }
        
            $data = $request->only('nombre', 'horaInicio', 'horaFinalizacion', 'direccion', 'aforo', 'precioPorHora', 'disponible', 'campoAbierto', 'iluminacion', 'suelo_id', 'deporte_id');

            $validator = Validator::make($data, [
                'nombre' => 'required|string|min:0|max:50',
                'horaInicio' => 'nullable|string',
                'horaFinalizacion' => 'nullable|string',
                'direccion' => 'required|string|min:0|max:100',
                'aforo' => 'nullable|integer',
                'precioPorHora' => 'required|numeric',
                'disponible' => 'required|boolean',
                'campoAbierto' => 'required|boolean',
                'iluminacion' => 'required|boolean',
                'suelo_id' => 'required|integer',
                'deporte_id' => 'required|integer',
                
            ]);

            if ($validator->fails())
                return response()->json(['error' => $validator->messages()], Response::HTTP_BAD_REQUEST);
            else {
                    if ($request->deporte_id == "5") {
                        if ($request->aforo != null && $request->horaInicio == null && $request->horaFinalizacion == null) {
                            $court->update([
                                'nombre' => $request->nombre,
                                'horaInicio' => $request->horaInicio,
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
                                'msg' => 'Pista actualizada con éxito',
                                'court' => $court
                            ], Response::HTTP_ACCEPTED);
                        } else {
                            return response()->json([
                                'msg' => 'El rocódromo debe tener aforo y no un horario',
                            ], Response::HTTP_BAD_REQUEST);
                        }
                    
                    } else {
                        if ($request->aforo == null && $request->horaInicio != null && $request->horaFinalizacion != null) {
                            $court->update([
                                'nombre' => $request->nombre,
                                'horaInicio' => $request->horaInicio,
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
                                'msg' => 'Pista actualizada con éxito',
                                'court' => $court
                            ], Response::HTTP_ACCEPTED);
                        } else {
                            return response()->json([
                                'msg' => 'Esta pista debe tener un horario y no aforo',
                            ], Response::HTTP_BAD_REQUEST);
                        }
                    }
        
            }
        }
        return response()->json([
            'msg' => 'Para hacer esta operación debes ser Administrador',
        ], Response::HTTP_FORBIDDEN);
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
    *     security={{"bearerAuth":{}}},
    *     summary="Añadir una pista nueva",
    *     @OA\Parameter(
    *        name="nombre",
    *        in="query",
    *        description="Nombre de la pista a editar",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="horaFinalizacion",
    *        in="query",
    *        description="A partir de esta hora se pueden hacer citas",
    *        required=false,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="direccion",
    *        in="query",
    *        description="Dirección de la pista",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="aforo",
    *        in="query",
    *        description="Capacidad máxima de personas en una pista",
    *        required=false,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="precioPorHora",
    *        in="query",
    *        description="Precio de la pista por hora",
    *        required=true,
    *        @OA\Schema(
    *            type="numeric"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="disponible",
    *        in="query",
    *        description="Especifica si la pista esta disponible para reservar",
    *        required=true,
    *        @OA\Schema(
    *            type="tinyInt"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="campoAbierto",
    *        in="query",
    *        description="Especifica si el campo es al aire libre o es en un sitio cerrado",
    *        required=true,
    *        @OA\Schema(
    *            type="tinyInt"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="iluminacion",
    *        in="query",
    *        description="Especifica si la pista tiene iluminación de calidad",
    *        required=true,
    *        @OA\Schema(
    *            type="tinyInt"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="suelo_id",
    *        in="query",
    *        description="ID del suelo",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="deporte_id",
    *        in="query",
    *        description="ID del deporte",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="
    *           Pista añadida con éxito"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="
    *           Error de validación
    *           El rocódromo debe tener aforo y no un horario
    *           Esta pista debe tener un horario y no aforo"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="
    *           Esta pista no existe"
    *     ),
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
                'disponible' => 'required|boolean',
                'campoAbierto' => 'required|boolean',
                'iluminacion' => 'required|boolean',
                'suelo_id' => 'required|integer',
                'deporte_id' => 'required|integer',
                
            ]);

            if ($validator->fails())
                return response()->json(['error' => $validator->messages()], Response::HTTP_BAD_REQUEST);
            else {
                    if ($request->deporte_id == "5") {
                        if ($request->aforo != null && $request->horaInicio == null && $request->horaFinalizacion == null) {
                            $court = Court::create([
                                'nombre' => $request->nombre,
                                'horaInicio' => $request->horaInicio,
                                'rutaImagen' => "public/images/court/default.svg",
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
                            ], Response::HTTP_ACCEPTED);
                        } else {
                            return response()->json([
                                'msg' => 'El rocódromo debe tener aforo y no un horario',
                            ], Response::HTTP_BAD_REQUEST);
                        }
                    
                    } else {
                        if ($request->aforo == null && $request->horaInicio != null && $request->horaFinalizacion != null) {
                            $court = Court::create([
                                'nombre' => $request->nombre,
                                'horaInicio' => $request->horaInicio,
                                'rutaImagen' => "public/images/court/default.svg",
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
                            ], Response::HTTP_ACCEPTED);
                        } else {
                            return response()->json([
                                'msg' => 'Esta pista debe tener un horario y no aforo',
                            ], Response::HTTP_BAD_REQUEST);
                        }
                    }
        
            }
        }
        return response()->json([
            'msg' => 'Para hacer esta operación debes ser Administrador',
        ], Response::HTTP_FORBIDDEN);
    }

    /*
    ###################################################
    #                   AÑADIR FOTO                   #
    ###################################################
    */

    /**
    * @OA\Post(
    *     path="/api/add-image/{id}",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Pista"},
    *     summary="Actualizar la foto de la pista",
    *     @OA\Parameter(
    *        name="id",
    *        in="path",
    *        description="ID de la pista",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="foto",
    *        in="query",
    *        description="Foto a actualizar de la pista",
    *        required=true,
    *        @OA\Schema(
    *            type="image"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="
    *           Foto actulizada con éxito"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="
    *           Esta pista no existe
    *           Error de validación"
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="
    *           Necesitas ser Administrador para realizar esta operación"
    *     ),
    *     @OA\Response(
    *         response=422,
    *         description="
    *           Foto no válida"
    *     ),
    * )
    */
    public function addImage(Request $request, $id) {
        if ($this->user->rol_id == 1) {
            try {
                $court = Court::findOrFail($id);
            } catch (Exception $e) {
                return response()->json([
                    'msg' => 'Esta pista no existe'
                ], Response::HTTP_BAD_REQUEST);
            }

            $data = $request->only('foto');

            $validator = Validator::make($data, [
                'foto' => 'image|mimes:jpg,png,jpeg,svg|max:3096|dimensions:min_width=350,min_height=350,max_width=4000,max_height=4000',
            ]);

            if ($validator->fails())
                return response()->json(['error' => $validator->messages()], Response::HTTP_BAD_REQUEST);
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
                    ], Response::HTTP_ACCEPTED);
                }
        
                return response()->json([
                    'msg' => 'Foto no válida',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } else {
            return response()->json([
                'msg' => 'Necesitas ser Administrador para realizar esta operación'
            ], Response::HTTP_FORBIDDEN);
        }
    }

    /*
    ###################################################
    #                  ELIMINAR FOTO                  #
    ###################################################
    */

    /**
    * @OA\Post(
    *     path="/api/remove-image/{id}",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Pista"},
    *     summary="Eliminar foto de una pista",
    *     @OA\Parameter(
    *        name="id",
    *        in="path",
    *        description="ID de la pista",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="Se ha eliminado la foto correctamente"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="
    *           Esta pista no existe
    *           No se ha podido eliminar la foto porque no existe"
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="
    *           Necesitas ser Administrador para realizar esta operación"
    *     )
    * )
    */
    public function removeImage(Request $request, $id) {
        if ($this->user->rol_id == 1) {
            try {
                $court = Court::findOrFail($id);
            } catch (Exception $e) {
                return response()->json([
                    'msg' => 'Esta pista no existe'
                ], Response::HTTP_BAD_REQUEST);
            }

            $file_name = explode("/", $court->rutaImagen)[3];
        
            if (File::exists(public_path('images/court/'.$file_name))) {
                File::delete(public_path('images/court/'.$file_name));
                $court->rutaImagen = 'public/images/court/default.svg';
                $court->save();

                return response()->json([
                    'msg' => 'Se ha eliminado la foto correctamente'
                ], Response::HTTP_ACCEPTED);
            }

            return response()->json([
                'msg' => 'No se ha podido eliminar la foto porque no existe'
            ], Response::HTTP_BAD_REQUEST);
        } else {
            return response()->json([
                'msg' => 'Necesitas ser Administrador para realizar esta operación'
            ], Response::HTTP_FORBIDDEN);
        }
    }
}
