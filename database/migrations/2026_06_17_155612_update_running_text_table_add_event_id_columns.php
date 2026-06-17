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
        Schema::table('running_texts', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable()->constrained('db_general.dbo.location_assets')->onDelete('cascade');
            $table->foreignId('event_id')->nullable()->references('id')->on('events')->onDelete('set null');
            $table->string('created_by');
            $table->string('updated_by')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('running_texts', function (Blueprint $table) {
            $table->dropColumn('location_id');
            $table->dropForeign(['event_id']);
            $table->dropColumn('event_id');
            $table->dropColumn('updated_by');
            $table->dropColumn('created_by');
            $table->dropSoftDeletes();
        });
    }
};
