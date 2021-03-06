<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaDetalleUsuario extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admision.detalle_usuarios', function (Blueprint $table) {
            $table->id('idusuario_det');
            $table->dateTime('ultimo_inicio')->nullable();
            $table->char('estado',1);
            $table->bigInteger('id_seccion')->nullable();
            $table->foreign('id_seccion')->references('id_seccion')->on('admision.adm_seccion_estudios')->onDelete('cascade');
            $table->bigInteger('id_usuario');
            $table->bigInteger('id_tipo_usuario');
            $table->foreign('id_usuario')->references('id')->on('admision.adm_usuario')->onDelete('cascade');
            $table->foreign('id_tipo_usuario')->references('id_tipo_usuario')->on('admision.tipo_usuarios')->onDelete('cascade');
            $table->string('imagen')->nullable();
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
}
