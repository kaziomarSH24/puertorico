<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name'); // Ex: "Weekly", "Monthly", "Yearly"
            $table->decimal('price', 8, 2); // Ex: 10.00, 20.00
            $table->integer('audio_limit'); // Ex: 100, 300, Unlimited (-1 for unlimited)
            $table->integer('order')->default(0); // Ex: 0, 1, 2
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_plans');
    }
};
