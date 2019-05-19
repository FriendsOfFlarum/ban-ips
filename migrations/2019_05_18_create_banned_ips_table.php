<?php

use Flarum\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

return Migration::createTable('banned_ips', function (Blueprint $table) {
    $table->increments('id');
    $table->unsignedInteger('creator_id');
    $table->string('address')->unique();
    $table->string('reason')->nullable();
    $table->unsignedInteger('user_id')->nullable();
    $table->timestamp('created_at');
    $table->timestamp('deleted_at')->nullable();

    $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
});
