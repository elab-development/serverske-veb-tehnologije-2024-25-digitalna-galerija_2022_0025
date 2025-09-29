<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artworks', function (Blueprint $table) {
            $table->id();
            $table->string('naziv');
            $table->text('opis')->nullable();
            $table->string('file_path');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('artworks', function (Blueprint $table) {
            $table->text('naziv')->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artworks');

        Schema::table('artworks', function (Blueprint $table) {
            $table->string('naziv')->change();
        });
    }
};
