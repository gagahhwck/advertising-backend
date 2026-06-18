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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->references('id')->on('contents')->onDelete('no action');
            $table->string('order_code')->unique();
            $table->bigInteger('amount');
            $table->enum('status', [
                'pending',
                'paid',
                'failed',
                'expired',
                'rejected'
            ])->default('pending');
            $table->text('reason')->nullable();
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('reject_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
