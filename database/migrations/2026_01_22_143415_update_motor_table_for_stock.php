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
        Schema::table('motor', function (Blueprint $table) {
            if (!Schema::hasColumn('motor', 'stok')) {
                $table->integer('stok')->default(0)->after('harga_sewa_per_hari');
            }
            if (Schema::hasColumn('motor', 'spek_mesin')) {
                $table->dropColumn('spek_mesin');
            }
            if (Schema::hasColumn('motor', 'kapasitas_tangki')) {
                $table->dropColumn('kapasitas_tangki');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('motor', function (Blueprint $table) {
            $table->dropColumn('stok');
            $table->string('spek_mesin', 50)->nullable();
            $table->string('kapasitas_tangki', 50)->nullable();
        });
    }
};
