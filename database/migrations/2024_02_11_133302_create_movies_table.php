<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('picture')->nullable();
            $table->text('description')->nullable();
            $table->string('year')->nullable();
            $table->string('score')->nullable();
            $table->text('trailer')->nullable();
            $table->json('content')->nullable();
            $table->boolean('is_show')->default(false);
            $table->integer('number_of_page');
            $table->integer('number_of_movie');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
