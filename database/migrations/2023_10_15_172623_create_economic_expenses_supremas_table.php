<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEconomicExpensesSupremasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('economic_expenses_supremas', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->dateTime('date_time');
            $table->string('moneda');
            $table->double('mount');
            $table->text('titulo')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('status')->nullable();
            $table->text('attached_files')->nullable();
            $table->text('metadata')->nullable();
            $table->string('code_user');
            $table->string('code_company')->nullable();
            $table->integer('id_exp');
            $table->string('entidad')->nullable();
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
        Schema::dropIfExists('economic_expenses_supremas');
    }
}
