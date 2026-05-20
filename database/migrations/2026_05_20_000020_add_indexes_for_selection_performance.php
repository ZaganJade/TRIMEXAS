<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->index('user_id', 'students_user_id_extra_index');
        });

        Schema::table('selection_results', function (Blueprint $table) {
            $table->index(['batch_id', 'eligible'], 'selection_results_batch_eligible_index');
        });

        Schema::table('selection_rule_evaluations', function (Blueprint $table) {
            $table->index(['batch_id', 'rule_code'], 'sre_batch_rule_extra_index');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_user_id_extra_index');
        });

        Schema::table('selection_results', function (Blueprint $table) {
            $table->dropIndex('selection_results_batch_eligible_index');
        });

        Schema::table('selection_rule_evaluations', function (Blueprint $table) {
            $table->dropIndex('sre_batch_rule_extra_index');
        });
    }
};
