<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offline_content_bundles', function (Blueprint $table) {
            $table->id();
            $table->string('content_type', 32);
            $table->unsignedBigInteger('content_id');
            $table->string('bundle_path', 500)->nullable();
            $table->string('bundle_hash', 64)->nullable();
            $table->unsignedInteger('asset_count')->default(0);
            $table->unsignedBigInteger('bytes')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamp('built_at')->nullable();
            $table->timestamps();

            $table->unique(['content_type', 'content_id']);
            $table->index('content_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offline_content_bundles');
    }
};
