<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spot_difference_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spot_difference_id')->constrained()->cascadeOnDelete();

            // Position as percentage of image dimensions (0-100)
            // so it works at any screen size
            $table->decimal('x_percent', 5, 2); // e.g. 45.50 = 45.5% from left
            $table->decimal('y_percent', 5, 2); // e.g. 32.10 = 32.1% from top
            $table->decimal('radius_percent', 4, 2)->default(5.00); // circle radius as % of image width

            $table->string('label')->nullable(); // e.g. "Missing bird"
            $table->unsignedInteger('order_index')->default(0);

            $table->timestamps();

            $table->index(['spot_difference_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spot_difference_zones');
    }
};
