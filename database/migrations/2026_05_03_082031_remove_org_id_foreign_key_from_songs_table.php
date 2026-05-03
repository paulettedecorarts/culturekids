<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['org_id']);
            
            // Keep the column but make it truly nullable without constraint
            // Songs are universal content, not tied to specific organizations
        });
    }

    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            // Re-add the foreign key constraint if rolling back
            $table->foreign('org_id')->references('id')->on('organisations')->nullOnDelete();
        });
    }
};