<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    if (!Schema::hasColumn('motor', 'stok')) {
        Schema::table('motor', function (Blueprint $table) {
            $table->integer('stok')->default(0)->after('harga_sewa_per_hari');
        });
        echo "Added stok column.\n";
    } else {
        echo "stok column already exists.\n";
    }

    $columnsToDrop = [];
    if (Schema::hasColumn('motor', 'spek_mesin')) {
        $columnsToDrop[] = 'spek_mesin';
    }
    if (Schema::hasColumn('motor', 'kapasitas_tangki')) {
        $columnsToDrop[] = 'kapasitas_tangki';
    }

    if (!empty($columnsToDrop)) {
        Schema::table('motor', function (Blueprint $table) use ($columnsToDrop) {
            $table->dropColumn($columnsToDrop);
        });
        echo "Dropped columns: " . implode(', ', $columnsToDrop) . ".\n";
    } else {
        echo "No columns to drop.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
