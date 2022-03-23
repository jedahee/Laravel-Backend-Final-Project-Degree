<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Suelos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suelos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
        });

        // Datos insertados después de la migración

        DB::table("suelos")->insert([
            ["nombre" => "césped"],
            ["nombre" => "arena"],
            ["nombre" => "madera"],
            ["nombre" => "cemento"],
            ["nombre" => "sintético"],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suelos');
    }
}
