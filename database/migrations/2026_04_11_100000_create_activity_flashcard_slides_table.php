<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_flashcard_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->unsignedInteger('order_index')->default(0);
            $table->string('emoji', 32)->nullable();
            $table->text('front_label')->nullable();
            $table->text('back_label')->nullable();
            $table->string('phonetic', 255)->nullable();
            $table->string('image_path', 500)->nullable();
            $table->string('audio_path', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['activity_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_flashcard_slides');
    }
};
