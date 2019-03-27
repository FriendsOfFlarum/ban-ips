<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        $schema->create('banned_ip_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('post_id')->nullable();
            $table->string('ip_address');
            $table->timestamp('created_at');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('set null');
        });
    },
    'down' => function (Builder $schema) {
        $schema->drop('banned_ip_addresses');
    }
];
