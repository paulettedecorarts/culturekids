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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('impersonator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // LOGIN, LOGOUT, CREATE, UPDATE, DELETE, MODULE_TOGGLE, IMPERSONATE, etc.
            $table->string('resource')->nullable(); // users/123, organisations/45, modules/comics
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('payload')->nullable(); // before/after data
            $table->string('status')->default('success'); // success, failed, error
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
