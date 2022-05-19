<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Reservas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
                    
            $table->timestamps();

            $table->string('horaInicio')->nullable();
            $table->string('horaFinalizacion')->nullable();
            $table->date('fechaCita');
            $table->integer('numLista')->nullable();
            
            $table->unsignedBigInteger('users_id');
            $table->unsignedBigInteger('pistas_id');
            
            // Relaciones
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('pistas_id')->references('id')->on('pistas')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reservas');
    }
}
