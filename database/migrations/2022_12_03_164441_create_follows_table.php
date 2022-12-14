<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->unsignedBigInteger("id_target");
            $table->unsignedBigInteger("id_follower");
            $table->timestamps();

            // foreign key
            $table->foreign("id_target")->references("id")->on("users");
            $table->foreign("id_follower")->references("id")->on("users");

            // set primary key
            $table->primary(["id_target", "id_follower"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('follows');
    }
};
