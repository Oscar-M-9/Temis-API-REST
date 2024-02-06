<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkFlowsStagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_flows_stages', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->nullable();
            $table->string('nombre');
            $table->integer('id_workflow');
            $table->text('code_user');
            $table->text('code_company');
            $table->text('metadata')->nullable();
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
        Schema::dropIfExists('work_flows_stages');
    }
}
