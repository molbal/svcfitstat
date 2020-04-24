<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCredentials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->string("APP_ID", 16)->primary();
            $table->string("APP_SECRET", 64);
            $table->string("NOTIFY_EMAIL", 256);
            $table->unsignedBigInteger("USER_ID");
            $table->unsignedInteger("QUOTA");
            $table->string("CALLBACK_URL", 256);

            $table->foreign("USER_ID")->references("id")->on("users");
        });

        Schema::create('rolling', function (Blueprint $table) {
            $table->bigIncrements("ID");
            $table->string("APP_ID", 16)->index();
            $table->timestamp("SUBMITTED")->useCurrent();

            $table->foreign("APP_ID")->references("APP_ID")->on("applications");
        });

        Schema::create('historic', function (Blueprint $table) {
            $table->string("APP_ID", 16);
            $table->unsignedInteger("YEAR");
            $table->unsignedInteger("MONTH");
            $table->timestamp("SAVED")->useCurrent();
            $table->unsignedInteger("USED");
            $table->unsignedInteger("LEFT");

            $table->primary(["APP_ID", "YEAR", "MONTH"]);
            $table->foreign("APP_ID")->references("APP_ID")->on("applications");
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rolling');
        Schema::dropIfExists('historic');
        Schema::dropIfExists('applications');
    }
}
