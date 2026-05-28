<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_content_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_profile_id')->constrained()->cascadeOnDelete();
            $table->string('content_type', 32);
            $table->unsignedBigInteger('content_id');
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->unsignedInteger('current_position')->default(0);
            $table->unsignedInteger('total_positions')->default(0);
            $table->unsignedInteger('stars_earned')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->string('completion_idempotency_key')->nullable()->unique();
            $table->timestamps();

            $table->unique(['child_profile_id', 'content_type', 'content_id'], 'child_content_progress_unique');
            $table->index(['child_profile_id', 'status']);
            $table->index(['content_type', 'content_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_content_progress');
    }
};
