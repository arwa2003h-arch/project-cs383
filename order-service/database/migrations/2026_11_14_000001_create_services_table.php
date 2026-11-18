<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Laundry services table (washing, ironing, dry cleaning ...)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name_en', 100);
            $table->string('name_ar', 100);
            $table->decimal('unit_price', 8, 2); // price for one item
            $table->boolean('is_active')->default(true); // false = soft deleted
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
