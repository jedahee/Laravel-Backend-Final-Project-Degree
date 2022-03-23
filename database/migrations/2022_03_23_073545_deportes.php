<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Deportes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deportes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
        });

        // Datos insertados después de la migración

        DB::table("deportes")->insert([
            ["nombre" => "fútbol"],
            ["nombre" => "baloncesto"],
            ["nombre" => "pádel"],
            ["nombre" => "tenis"],
            ["nombre" => "rocódromo"],
            ["nombre" => "voleibol"],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deportes');
    }
}
