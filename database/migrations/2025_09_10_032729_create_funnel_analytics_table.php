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
        Schema::create('funnel_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('session_id'); // Track user sessions
            $table->string('page_type'); // home, step1, step2, step3, success
            $table->string('action_type'); // visit, click, conversion
            $table->string('utm_source')->nullable(); // Traffic source
            $table->string('utm_medium')->nullable(); // Traffic medium
            $table->string('utm_campaign')->nullable(); // Campaign name
            $table->string('referrer')->nullable(); // Referrer URL
            $table->string('user_agent')->nullable(); // Browser info
            $table->string('ip_address')->nullable(); // IP for geo tracking
            $table->json('metadata')->nullable(); // Additional data
            $table->timestamps();
            
            $table->index(['session_id', 'page_type']);
            $table->index(['action_type', 'created_at']);
            $table->index('utm_campaign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funnel_analytics');
    }
};
