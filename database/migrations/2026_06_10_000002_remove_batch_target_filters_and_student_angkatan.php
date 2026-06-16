<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('selection_batches', 'periode') && Schema::hasColumn('selection_batches', 'tahun_akademik')) {
            try {
                Schema::table('selection_batches', function (Blueprint $table) {
                    $table->dropIndex(['periode', 'tahun_akademik']);
                });
            } catch (Throwable) {
                // Fresh installs may not have this legacy index.
            }
        }

        $batchColumns = array_values(array_filter(
            ['target_semester', 'target_angkatan'],
            fn (string $column): bool => Schema::hasColumn('selection_batches', $column),
        ));

        if ($batchColumns !== []) {
            Schema::table('selection_batches', function (Blueprint $table) use ($batchColumns) {
                $table->dropColumn($batchColumns);
            });
        }

        if (Schema::hasColumn('students', 'angkatan')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('angkatan');
            });
        }
    }

    public function down(): void
    {
        Schema::table('selection_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('selection_batches', 'target_semester')) {
                $table->unsignedTinyInteger('target_semester')->nullable();
            }

            if (! Schema::hasColumn('selection_batches', 'target_angkatan')) {
                $table->unsignedSmallInteger('target_angkatan')->nullable();
            }
        });

        if (Schema::hasColumn('selection_batches', 'periode') && Schema::hasColumn('selection_batches', 'tahun_akademik')) {
            try {
                Schema::table('selection_batches', function (Blueprint $table) {
                    $table->index(['periode', 'tahun_akademik']);
                });
            } catch (Throwable) {
                // Index may already exist when rolling back older databases.
            }
        }

        if (! Schema::hasColumn('students', 'angkatan')) {
            Schema::table('students', function (Blueprint $table) {
                $table->unsignedSmallInteger('angkatan')->nullable()->after('semester');
            });
        }
    }
};
