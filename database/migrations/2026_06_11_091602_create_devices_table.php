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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('location_id')->constrained('db_general.dbo.location_assets')->onDelete('cascade');
            $table->string('device_name');
            $table->string('device_code')->unique();
            $table->enum('orientation', ['portrait', 'landscape']);
            $table->integer('width');
            $table->integer('height');
            $table->boolean('is_online')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
