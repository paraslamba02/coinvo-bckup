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
        Schema::table('tracking_links', function (Blueprint $table) {
            // Remove UTM fields
            $table->dropColumn(['utm_source', 'utm_medium', 'utm_campaign']);
            
            // Rename tracking_code to slug for clarity
            $table->renameColumn('tracking_code', 'slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracking_links', function (Blueprint $table) {
            // Rename back to tracking_code
            $table->renameColumn('slug', 'tracking_code');
            
            // Add back UTM fields
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable(); 
            $table->string('utm_campaign')->nullable();
        });
    }
};
