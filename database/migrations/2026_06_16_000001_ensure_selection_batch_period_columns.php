<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('selection_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('selection_batches', 'periode')) {
                $table->string('periode', 16)->nullable()->after('label');
            }

            if (! Schema::hasColumn('selection_batches', 'tahun_akademik')) {
                $table->unsignedSmallInteger('tahun_akademik')->nullable()->after('periode');
            }
        });
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['periode', 'tahun_akademik'],
            fn (string $column): bool => Schema::hasColumn('selection_batches', $column),
        ));

        if ($columns !== []) {
            Schema::table('selection_batches', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
