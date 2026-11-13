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
        Schema::create('inks', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('brand');
            $table->string('model');
            $table->string('color')->default('Black');
            $table->string('color_code')->default('#212529');
            $table->string('type')->default('Toner Cartridge');
            $table->string('unit')->default('ตลับ');
            $table->text('compatible_printers')->nullable();
            $table->string('location')->nullable();
            $table->integer('current_stock')->default(0);
            $table->integer('min_stock')->default(2);
            $table->decimal('price_per_unit', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inks');
    }
};
