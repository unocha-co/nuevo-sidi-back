<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SistemaPermisosUsuariosPerfiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('resource');
            $table->timestamps();
        });

        Schema::create('permissions_profile', function (Blueprint $table) {
            $table->integer('id_permission');
            $table->integer('id_profile');
            $table->boolean('create')->default(false);
            $table->boolean('read')->default(false);
            $table->boolean('update')->default(false);
            $table->boolean('delete')->default(false);
            $table->primary(['id_permission', 'id_profile']);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('auth0_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permissions');
    }
}
