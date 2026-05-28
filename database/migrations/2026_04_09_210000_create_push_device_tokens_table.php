<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organisation_id')->nullable()->constrained('organisations')->nullOnDelete();
            $table->string('platform', 20); // ios|android|web
            $table->text('token');
            $table->string('device_name')->nullable();
            $table->string('app_version')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['organisation_id', 'is_active']);
            $table->index(['user_id', 'is_active']);
            
            if (config('database.default') !== 'sqlite') {
                $table->index([DB::raw('token(768)')], 'push_device_tokens_token_index');
            } else {
                $table->index(['token'], 'push_device_tokens_token_index');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_device_tokens');
    }
};
