<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSocialLogins extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('users_social_logins', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->string('token');
            $table->string('provider');
            $table->json('data');
            $table->timestamps();

            $table->foreign("user_id")->references("id")->on("users")->onDelete('cascade');
        });

        \Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_email_unique');
            $table->string('email')->unique()->nullable()->change();
            $table->string('nickname')->nullable();
            $table->string('avatar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::drop('users_social_logins');
        \Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('nickname');
            $table->dropColumn('avatar');
        });
    }
}
