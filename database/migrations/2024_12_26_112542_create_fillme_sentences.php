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
        Schema::create('fillme_sentences', function (Blueprint $table) {
            $table->uuid();
            $table->text('sentence');
            $table->string('words');
            $table->tinyInteger('length');
            $table->tinyInteger('category');
            $table->string('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fillme_sentences');
    }
};
