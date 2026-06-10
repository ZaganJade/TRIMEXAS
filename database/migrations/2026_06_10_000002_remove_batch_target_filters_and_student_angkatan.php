<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('selection_batches', function (Blueprint $table) {
            $table->dropIndex(['periode', 'tahun_akademik']);
            $table->dropColumn(['target_semester', 'target_angkatan']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('angkatan');
        });
    }

    public function down(): void
    {
        Schema::table('selection_batches', function (Blueprint $table) {
            $table->unsignedTinyInteger('target_semester')->nullable()->after('tahun_akademik');
            $table->unsignedSmallInteger('target_angkatan')->nullable()->after('target_semester');
            $table->index(['periode', 'tahun_akademik']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->unsignedSmallInteger('angkatan')->nullable()->after('semester');
        });
    }
};
