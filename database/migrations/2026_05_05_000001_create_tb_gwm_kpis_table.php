<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_gwm_kpis', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('month')->unsigned(); // 1-12
            $table->smallInteger('year')->unsigned(); // เช่น 2026
            $table->decimal('sale_kpi',       5, 2)->default(0);
            $table->decimal('ssi',            5, 2)->default(0);
            $table->decimal('after_sale_kpi', 5, 2)->default(0);
            $table->decimal('csi',            5, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_gwm_kpis');
    }
};
