<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuscripcionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suscripcions', function (Blueprint $table) {
            $table->id();
            $table->integer('price')->nullable();
            $table->string('type_suscripcion')->nullable();
            $table->integer('dias_suscripcion')->nullable();
            $table->string('accept_terms_and_conditions')->nullable();
            $table->date('current_period_start')->nullable();
            $table->date('current_period_end')->nullable();
            $table->date('cancel_at_period_end')->nullable();
            $table->date('cancel_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->integer("limit_users")->nullable();
            $table->integer("limit_workflows")->nullable();
            $table->string("access_judicial")->nullable();
            $table->string("access_indecopi")->nullable();
            $table->string("access_suprema")->nullable();
            $table->string("access_sinoe")->nullable();
            $table->integer("limit_judicial")->nullable();
            $table->integer("limit_indecopi")->nullable();
            $table->integer("limit_suprema")->nullable();
            $table->integer("limit_sinoe")->nullable();
            $table->integer("limit_credencial_sinoe")->nullable();
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
        Schema::dropIfExists('suscripcions');
    }
}
