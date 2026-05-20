<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('category', 16); // akademis | non_akademis
            $table->string('level', 24); // internasional | nasional | provinsi | kabupaten
            $table->string('rank', 24); // juara_1 | juara_2 | juara_3 | partisipasi
            $table->unsignedSmallInteger('year');
            $table->decimal('score', 5, 2)->default(0); // skor entri (akan dihitung scorer)
            $table->boolean('verified_by_admin')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_note')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_achievements');
    }
};
