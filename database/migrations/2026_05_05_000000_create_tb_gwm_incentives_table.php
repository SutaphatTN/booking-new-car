<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_gwm_incentives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subcarmodel_id');
            $table->tinyInteger('month')->unsigned();  // 1-12
            $table->smallInteger('year')->unsigned();  // เช่น 2026
            $table->decimal('fixed', 5, 2)->default(0);
            $table->decimal('lt70', 5, 2)->default(0);
            $table->decimal('gte70_lte85', 5, 2)->default(0);
            $table->decimal('gt85_lte100', 5, 2)->default(0);
            $table->decimal('gt100_lte120', 5, 2)->default(0);
            $table->decimal('gte120', 5, 2)->default(0);
            $table->decimal('max_val', 5, 2)->default(0);
            $table->integer('monthly_target')->default(0); // Monthly target (Units)
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['subcarmodel_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_gwm_incentives');
    }
};
