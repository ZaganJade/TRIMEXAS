<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('selection_batches', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('periode', 16)->nullable();
            $table->unsignedInteger('tahun_akademik')->nullable();
            $table->foreignId('triggered_by')->constrained('users')->restrictOnDelete();
            $table->string('status', 16)->default('queued'); // queued | running | completed | failed
            $table->unsignedInteger('total_candidates')->default(0);
            $table->unsignedInteger('total_eligible')->default(0);
            $table->unsignedInteger('total_ineligible')->default(0);
            $table->unsignedInteger('processed_count')->default(0);
            $table->jsonb('snapshot_fuzzy_sets')->nullable();
            $table->jsonb('snapshot_rules')->nullable();
            $table->jsonb('snapshot_thresholds')->nullable();
            $table->jsonb('error_summary')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('selection_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('selection_batches')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->boolean('eligible')->default(false);
            $table->jsonb('ineligibility_reasons')->nullable();
            $table->jsonb('input_snapshot')->nullable(); // {ipk, penghasilan, ...} + memberships
            $table->decimal('score', 6, 3)->nullable(); // Z, 0..100
            $table->string('category', 24)->nullable(); // tidak_layak | dipertimbangkan | layak
            $table->unsignedInteger('rank')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'student_id']);
            $table->index(['batch_id', 'score']);
        });

        Schema::create('selection_rule_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('selection_batches')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('rule_code', 16);
            $table->string('consequent', 24);
            $table->decimal('alpha', 6, 4); // 0..1
            $table->decimal('z', 6, 3); // output
            $table->timestamps();

            $table->index(['batch_id', 'student_id']);
            $table->index(['batch_id', 'rule_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('selection_rule_evaluations');
        Schema::dropIfExists('selection_results');
        Schema::dropIfExists('selection_batches');
    }
};
