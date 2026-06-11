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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
            $table->string('invoice_number');
            $table->bigInteger('amount');
            $table->string('evidence')->nullable();
            $table->enum('payment_method',[
                'manual',
                'va',
                'qris',
                'ewallet'
            ]);
            $table->enum('status',[
                'pending',
                'paid',
                'failed',
                'refund'
            ]);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
