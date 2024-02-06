<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountGoogleCalendarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_google_calendars', function (Blueprint $table) {
            $table->id();
            $table->text("access_token");
            $table->integer("expires_in");
            $table->text("refresh_token");
            $table->text("scope");
            $table->text("token_type");
            $table->text("created");
            $table->text("client_id");
            $table->text("client_secret");
            $table->text("id_calendar");
            $table->text("iam_email")->nullable();
            $table->integer("id_user");
            $table->text("code_user");
            $table->text("code_company");
            $table->text("metadata")->nullable();
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
        Schema::dropIfExists('account_google_calendars');
    }
}
