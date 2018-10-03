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
        Schema::table('project_beneficiaries', function (Blueprint $table) {
             $table->integer('group_id')->nullable()->default(NULL)->change();
             $table->string('gender')->nullable()->default(NULL)->change();
             $table->string('age')->nullable()->default(NULL)->change();
             $table->string('type')->nullable()->default(NULL)->change();
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
