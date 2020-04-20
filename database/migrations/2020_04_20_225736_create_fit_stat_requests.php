<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFitStatRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fit_stat_requests', function (Blueprint $table) {
            $table->unsignedBigInteger("id")->primary();
            $table->string("eft", 2048);
            $table->unsignedBigInteger("userId")->index();
            $table->boolean("sync");
            /*
            $id
$eft
$userId
$sync
            */
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("fit_stat_requests");
    }
}
