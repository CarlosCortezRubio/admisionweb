<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admision.adm_cupos', function (Blueprint $table) {
            $table->id('id_cupos');
            $table->integer('cant_cupo');
            $table->string('observacion');
            $table->char('estado',10);
            $table->bigInteger('id_periodo');
            $table->foreign('id_periodo')->references('id_periodo')->on('admision.adm_periodo');
            $table->bigInteger('user_regi');
            $table->bigInteger('user_actu');
            $table->foreign('user_regi')->references('id')->on('admision.adm_usuario');
            $table->foreign('user_actu')->references('id')->on('admision.adm_usuario');
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
        Schema::dropIfExists('admision.adm_cupos');
    }
}
