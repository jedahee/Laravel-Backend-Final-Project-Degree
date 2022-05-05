<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use App\Models\User;
use App\Models\Comment;
use App\Models\Court;
use Exception;
use DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
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
    #       OBTENER COMENTARIOS DE UNA PISTA          #
    ###################################################
    */

    /**
    * @OA\Get(
    *     path="/api/get-comments/{court_id}",
    *     tags = {"Comentarios"},
    *     summary="Obtener todos los comentarios de una pista",
    *     @OA\Parameter(
    *        name="court_id",
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
    *           Los comentarios se han obtenido correctamente
    *           No hay comentarios de esta pista"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="
    *           La pista no existe"
    *     )
    * )
    */
    public function getComments(Request $request, $court_id)
    {
        $msg = "";

        try {
            $comments = Comment::where('pistas_id', $court_id)->get();
        } catch (Exception $e) {
            return response()->json([
                'msg' => 'La pista no existe'
            ], Response::HTTP_NOT_FOUND);
        }

        $msg = count($comments) > 0 ? "Los comentarios se han obtenido correctamente" : "No hay comentarios de esta pista";

        return response()->json([
            'msg' => $msg,
            'comments' => $comments,
        ], Response::HTTP_ACCEPTED);

    }
    
    /*
    ###################################################
    #          AÑADIR COMENTARIO A UNA PISTA          #
    ###################################################
    */

    /**
    * @OA\Post(
    *     path="/api/add-comment/{court_id}",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Comentarios"},
    *     summary="Añadir un comentario",
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
    *        name="texto",
    *        in="query",
    *        description="Texto del comentario a añadir",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="like",
    *        in="query",
    *        description="Este parámetro representa si el comentario es positivo o negativo",
    *        required=true,
    *        @OA\Schema(
    *            type="bool"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="
    *           Se ha publicado el comentario correctamente"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="
    *           Error de validación"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="
    *           No existe la pista donde se quiere publicar el comentario"
    *     ),
    * )
    */
    public function addComment(Request $request, $court_id)
    {
        $data = $request->only('texto', 'like');

        $validator = Validator::make($data, [
            'texto' => 'required|string|min:0',
            'like' => 'required|boolean',
        ]);

        if ($validator->fails())
            return response()->json(['error' => $validator->messages()], Response::HTTP_BAD_REQUEST);
        else {
            $court = Court::where('id', $court_id)->first();

            if ($court) {
                Comment::create([
                    'texto' => $request->texto,
                    'like' => $request->like,
                    'users_id' => $this->user->id,
                    'pistas_id' => $court_id,
                ]);

                return response()->json([
                    'msg' => 'Se ha publicado el comentario correctamente',
                ], Response::HTTP_ACCEPTED);
            }

            return response()->json([
                'msg' => 'No existe la pista donde se quiere publicar el comentario',
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /*
    ###################################################
    #         ELIMINAR COMENTARIO DE UNA PISTA        #
    ###################################################
    */

    /**
    * @OA\Delete(
    *     path="/api/delete-comment/{id}",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Comentarios"},
    *     summary="Eliminar un comentario",
    *     @OA\Parameter(
    *        name="id",
    *        in="path",
    *        description="ID del comentario",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="
    *           Se ha borrado el comentario correctamente"
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="
    *           Debes ser Administrador, Moderador o propietario de este comentario para poder borrarlo"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="
    *           Este comentario no existe"
    *     )
    * )
    */
    public function deleteComment(Request $request, $id)
    {
        try {
            $comment = Comment::findOrFail($id);
        } catch (Exception $e) {
            return response()->json([
                'msg' => 'Este comentario no existe'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($this->user->rol_id == 1 || $this->user->rol_id == 2 || $this->user->id == $comment->users_id) {
            $comment->delete();

            return response()->json([
                'msg' => 'Se ha borrado el comentario correctamente'
            ], Response::HTTP_ACCEPTED);
        }

        return response()->json([
            'msg' => 'Debes ser Administrador, Moderador o propietario de este comentario para poder borrarlo'
        ], Response::HTTP_FORBIDDEN);
    }
}
