<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('nim', 32)->unique();
            $table->string('full_name');
            $table->unsignedTinyInteger('semester')->default(1);
            $table->string('status', 16)->default('aktif'); // aktif | cuti | lulus | keluar
            $table->decimal('ipk', 4, 2)->default(0); // 3.00 - 4.00
            $table->bigInteger('penghasilan_ortu')->default(0); // rupiah/bulan
            $table->unsignedTinyInteger('tanggungan')->default(0);
            $table->string('phone', 32)->nullable();
            $table->text('address')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'semester', 'ipk']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
