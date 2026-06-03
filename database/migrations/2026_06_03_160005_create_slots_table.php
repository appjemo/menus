<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label')->nullable(); // texto fijo opcional si no hay producto
            $table->integer('pos_x')->default(0); // coordenadas sobre el video base
            $table->integer('pos_y')->default(0);
            $table->unsignedInteger('font_size')->default(48);
            $table->string('font_color', 9)->default('#FFFFFF');
            $table->string('font_family')->nullable();
            $table->enum('align', ['left', 'center', 'right'])->default('left');
            $table->boolean('show_name')->default(false); // mostrar nombre del producto ademas del precio
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slots');
    }
};
