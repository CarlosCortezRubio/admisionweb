<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CrearDatosTipoUsuario extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admision.tipo_usuarios', function (Blueprint $table) {
            DB::table('admision.tipo_usuarios')
            ->insert([
                "descripcion" => "Administrador",
                "estado" => "A",
            ]);
            DB::table('admision.tipo_usuarios')
            ->insert([
                "descripcion" => "Secretario",
                "estado" => "A",
            ]);
            DB::table('admision.tipo_usuarios')
            ->insert([
                "descripcion" => "Jurado",
                "estado" => "A",
            ]);
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
