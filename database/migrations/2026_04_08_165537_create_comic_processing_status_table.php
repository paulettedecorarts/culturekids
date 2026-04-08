<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comic_processing_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comic_id')->constrained('comics')->cascadeOnDelete();
            $table->string('batch_id')->nullable()->index();
            $table->integer('total_files')->default(0);
            $table->integer('processed_files')->default(0);
            $table->integer('failed_files')->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('current_file')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['comic_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comic_processing_status');
    }
};
