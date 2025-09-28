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
        Schema::create('conversion_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tracking_link_id')->constrained()->onDelete('cascade');
            $table->foreignId('funnel_id')->constrained()->onDelete('cascade');
            $table->string('session_id')->index();
            $table->ipAddress('ip_address');
            $table->string('event_type'); // 'page_view', 'click', 'form_submit', 'purchase', etc.
            $table->string('event_category')->nullable(); // 'engagement', 'conversion', etc.
            $table->json('event_data')->nullable(); // Additional event-specific data
            $table->string('page_url')->nullable();
            $table->string('referrer_url')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();
            $table->integer('step_number')->default(1); // Track funnel step progression
            $table->decimal('time_spent', 8, 2)->nullable(); // Time spent in seconds
            $table->decimal('revenue', 10, 2)->nullable(); // Revenue for conversion events
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->timestamp('event_timestamp');
            $table->timestamps();

            $table->index(['tracking_link_id', 'session_id']);
            $table->index(['event_type', 'event_timestamp']);
            $table->index(['funnel_id', 'step_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversion_events');
    }
};
