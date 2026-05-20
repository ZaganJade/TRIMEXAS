<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 16)->default('mahasiswa')->after('email');
            $table->string('approval_status', 16)->default('pending')->after('role');
            $table->text('rejection_reason')->nullable()->after('approval_status');
            $table->foreignId('approved_by')->nullable()->after('rejection_reason')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');

            $table->index(['role', 'approval_status']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['role', 'approval_status']);
            $table->dropColumn([
                'role', 'approval_status', 'rejection_reason',
                'approved_by', 'approved_at',
            ]);
        });
    }
};
