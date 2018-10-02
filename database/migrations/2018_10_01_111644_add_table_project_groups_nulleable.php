<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableProjectGroupsNulleable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_groups', function (Blueprint $table) {
             $table->integer('group_id')->nullable()->change();
             $table->string('gender')->nullable()->change();
             $table->string('age')->nullable()->change();
             $table->string('type')->nullable()->change();
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
