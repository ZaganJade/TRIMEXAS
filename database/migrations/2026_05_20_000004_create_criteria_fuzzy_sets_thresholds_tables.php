<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lima kriteria fuzzy: ipk, penghasilan, prestasi_akademis, prestasi_non_akademis, tanggungan
        Schema::create('criteria', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique(); // ipk | penghasilan | prestasi_akademis | ...
            $table->string('name'); // human readable
            $table->decimal('domain_min', 14, 2);
            $table->decimal('domain_max', 14, 2);
            $table->string('unit', 32)->nullable(); // "skala 4", "rupiah/bulan", "poin", "orang"
            $table->unsignedTinyInteger('display_order')->default(0);
            $table->timestamps();
        });

        // Himpunan fuzzy per kriteria — biasanya 3 himpunan: rendah/sedang/tinggi (atau sedikit/sedang/banyak)
        Schema::create('fuzzy_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criterion_id')->constrained('criteria')->cascadeOnDelete();
            $table->string('name', 32); // rendah | sedang | tinggi | sedikit | banyak
            // Membership shape: linear_turun | segitiga | linear_naik
            $table->string('shape', 16);
            // Untuk linear_turun & linear_naik: a, b dipakai (c null).
            // Untuk segitiga: a, b, c dipakai.
            $table->decimal('a', 14, 4);
            $table->decimal('b', 14, 4);
            $table->decimal('c', 14, 4)->nullable();
            $table->timestamps();

            $table->unique(['criterion_id', 'name']);
        });

        // Threshold output: Tidak Layak < t1 ≤ Dipertimbangkan < t2 ≤ Layak
        Schema::create('output_thresholds', function (Blueprint $table) {
            $table->id();
            $table->decimal('threshold_1', 5, 2)->default(50);
            $table->decimal('threshold_2', 5, 2)->default(75);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('output_thresholds');
        Schema::dropIfExists('fuzzy_sets');
        Schema::dropIfExists('criteria');
    }
};
