<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->string('certificate_path')->nullable()->after('verification_note');
            $table->string('certificate_original_name')->nullable()->after('certificate_path');
            $table->unsignedBigInteger('certificate_size')->nullable()->after('certificate_original_name');
        });
    }

    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->dropColumn(['certificate_path', 'certificate_original_name', 'certificate_size']);
        });
    }
};
