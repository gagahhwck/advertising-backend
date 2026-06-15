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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->nullable()->references('id')->on('templates')->onDelete('set null');
            $table->foreignId('event_id')->references('id')->on('events')->onDelete('no action');
            $table->string('created_by');
            $table->string('updated_by')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('display_duration')->default(10);
            $table->integer('priority')->default(1);
            $table->boolean('is_active')->default(true);
            $table->enum('status', [
                'draft',
                'pending',
                'approved',
                'rejected',
                'scheduled',
                'active',
                'expired'
            ])->default('draft');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
