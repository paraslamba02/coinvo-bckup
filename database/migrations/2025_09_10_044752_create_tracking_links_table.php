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
        Schema::create('tracking_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funnel_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "YouTube Campaign"
            $table->string('source'); // e.g., "youtube", "instagram", "facebook"
            $table->string('tracking_code')->unique(); // e.g., "yt2024"
            $table->string('short_code')->unique()->nullable(); // e.g., "abc123" for /l/abc123
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable(); 
            $table->string('utm_campaign')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('click_count')->default(0);
            $table->unsignedBigInteger('unique_visitors')->default(0);
            $table->timestamp('last_clicked_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['funnel_id', 'is_active']);
            $table->index('tracking_code');
            $table->index('short_code');
            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_links');
    }
};
