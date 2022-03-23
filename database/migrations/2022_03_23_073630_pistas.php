<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Pistas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pistas', function (Blueprint $table) {
            $table->id();
            
            $table->string('nombre', 50);
            $table->string('horaInicio')->nullable();
            $table->string('horaFinalizacion')->nullable();
            $table->string('rutaImagen');
            $table->string('direccion', 100);
            
            $table->integer('aforo')->nullable();

            $table->decimal('precioPorHora');
            
            $table->boolean('disponible');
            $table->boolean('campoAbierto');
            $table->boolean('iluminacion');
            
            $table->unsignedBigInteger('suelo_id');
            $table->unsignedBigInteger('deporte_id');
            
            // Relaciones
            $table->foreign('suelo_id')->references('id')->on('suelos');
            $table->foreign('deporte_id')->references('id')->on('deportes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pistas');
    }
}
