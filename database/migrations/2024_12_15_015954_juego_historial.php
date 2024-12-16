<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('juego_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('juego_id')->constrained('juego')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->char('letra', 1);
            $table->string('palabra_actual');
            $table->integer('intentos_restantes');
            $table->boolean('acierto');
            $table->string('estado_juego');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
