<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slots', function (Blueprint $table) {
            // 'stacked' = nombre arriba del precio; 'inline' = mismo renglón
            $table->string('layout')->default('stacked')->after('show_name');
            // Ancho (px sobre el video base) para repartir nombre/precio en línea
            $table->unsignedInteger('box_width')->nullable()->after('layout');
        });
    }

    public function down(): void
    {
        Schema::table('slots', function (Blueprint $table) {
            $table->dropColumn(['layout', 'box_width']);
        });
    }
};
