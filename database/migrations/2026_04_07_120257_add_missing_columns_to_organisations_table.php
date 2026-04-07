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
        Schema::table('organisations', function (Blueprint $table) {
            if (Schema::hasColumn('organisations', 'slug')) {
                $table->renameColumn('slug', 'code');
            }
            $table->string('status')->default('active')->after('plan');
            $table->string('logo_url')->nullable()->after('status');
            $table->string('address')->nullable()->after('logo_url');
            $table->text('description')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->renameColumn('code', 'slug');
            $table->dropColumn(['status', 'logo_url', 'address', 'description']);
        });
    }
};
