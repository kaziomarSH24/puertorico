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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // $table->foreignId('pricing_plan_id')->constrained('pricing_plan')->onDelete('cascade');
            $table->string('payment_id')->nullable(); // Stripe Payment ID  
            $table->string('plan_name'); // Ex: "Weekly", "Monthly", "Yearly"
            $table->timestamp('start_date')->nullable(); // Subscription Start Date
            $table->decimal('price', 8, 2); // Ex: 10.00, 20.00
            $table->integer('audio_limit'); // Ex: 100, 300, Unlimited (-1 for unlimited)
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->timestamp('expires_at')->nullable(); // Subscription Expiry Date
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
