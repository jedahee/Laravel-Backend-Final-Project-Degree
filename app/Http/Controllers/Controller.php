<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
*  @OAS\SecurityScheme(
*      description="Se necesita estar logueado con email y contraseña para obtener el token",
*      securityScheme="bearerAuth",
*      type="http",
*      name="Authorization",
*      scheme="bearer",
*      in="header",
*      bearerFormat="JWT",       
*  )
*/
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
