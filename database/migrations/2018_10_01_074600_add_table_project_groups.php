<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableProjectGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('project_beneficiaries', function (Blueprint $table) {
            $table->unsignedInteger('project_id');
            $table->integer('group_id');
            $table->char('gender');
            $table->char('age');
            $table->char('type');
            $table->integer('number');
            $table->timestamps();
        });

        Schema::create('project_hrp', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('project_budget', function (Blueprint $table) {
            $table->unsignedInteger('project_id');
            $table->bigInteger('budget');
            $table->integer('budget_id');
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
        Schema::dropIfExists('project_groups');
    }
}
