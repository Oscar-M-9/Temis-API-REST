<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string("type_contact");
            $table->string("name")->nullable();
            $table->string("last_name")->nullable();
            $table->string("dni")->nullable();
            $table->date("birthdate")->nullable();
            $table->string("company")->nullable();
            $table->string("name_company")->nullable();
            $table->string("ruc")->nullable();
            $table->json("email");
            $table->json("phone");
            $table->json("address");
            $table->text('code_user');
            $table->text('code_company');

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
        Schema::dropIfExists('clientes');
    }
}
