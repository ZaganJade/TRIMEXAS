<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique(); // R001 .. R075
            // Antecedents: associative array {criterion_code: fuzzy_set_name}
            // e.g. {"ipk":"tinggi","penghasilan":"rendah",...}
            $table->jsonb('antecedents');
            // Consequent: layak | dipertimbangkan | tidak_layak
            $table->string('consequent', 24);
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'consequent']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};
