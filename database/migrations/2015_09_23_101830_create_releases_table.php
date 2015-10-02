<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReleasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('releases', function (Blueprint $table) {
            $table->increments('id');
            $table->string('commit');
            $table->string('status');
            $table->string('roles');
            $table->longText('raw_log');
            $table->unsignedInteger("inventory_id");
            $table->unsignedInteger("repo_id");
            $table->unsignedInteger("user_id");
            $table->integer('time')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamps();

            $table->foreign("inventory_id")->references("id")->on("inventories")->onDelete('cascade');
            $table->foreign("repo_id")->references("id")->on("repos")->onDelete('cascade');
            $table->foreign("user_id")->references("id")->on("users")->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('releases');
    }
}
