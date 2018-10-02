<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DefaultValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects_organizations', function (Blueprint $table) {
            $table->dropColumn('value');
        });

        Schema::table('projects_organizations', function (Blueprint $table) {
            $table->float('value', 8, 2)->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects_organizations', function (Blueprint $table) {
            //
        });
    }
}
