<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('native_name')->nullable();
            $table->string('code')->unique();
            $table->string('flag_emoji')->nullable();
            $table->unsignedTinyInteger('translation_coverage')->default(0);
            $table->boolean('audio_pack_available')->default(false);
            $table->enum('status', ['verified', 'partial', 'pending'])->default('pending');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(100);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        DB::table('languages')->insert([
            [
                'name' => 'Luganda',
                'native_name' => 'Luganda',
                'code' => 'lug-UG',
                'flag_emoji' => '🇺🇬',
                'translation_coverage' => 95,
                'audio_pack_available' => true,
                'status' => 'verified',
                'is_active' => true,
                'sort_order' => 10,
                'notes' => 'Primary launch language.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Acholi',
                'native_name' => 'Lwo',
                'code' => 'ach-UG',
                'flag_emoji' => '🇺🇬',
                'translation_coverage' => 62,
                'audio_pack_available' => false,
                'status' => 'partial',
                'is_active' => true,
                'sort_order' => 20,
                'notes' => 'Needs additional UI string localization.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
