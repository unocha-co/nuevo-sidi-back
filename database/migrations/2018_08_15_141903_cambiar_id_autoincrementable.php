<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CambiarIdAutoincrementable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('administrative_divisions', function (Blueprint $table) {
            $table->increments('id')->change();
        });
        Schema::table('contact_groups', function (Blueprint $table) {
            $table->increments('id')->change();
        });
        Schema::table('organization_project_relation', function (Blueprint $table) {
            $table->increments('id')->change();
        });
        Schema::table('organizations', function (Blueprint $table) {
            $table->increments('id')->change();
        });
        Schema::table('organization_types', function (Blueprint $table) {
            $table->increments('id')->change();
        });
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->increments('id')->change();
        });
        Schema::table('project_class', function (Blueprint $table) {
            $table->increments('id')->change();
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->increments('id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Contact_groups', function (Blueprint $table) {
            //
        });
    }
}
