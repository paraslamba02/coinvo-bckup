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
        Schema::create('link_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tracking_link_id')->constrained()->onDelete('cascade');
            $table->foreignId('funnel_id')->constrained()->onDelete('cascade'); // denormalized for faster queries
            $table->string('ip_address');
            $table->text('user_agent');
            $table->string('device_type')->nullable(); // mobile/desktop/tablet
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->text('referrer')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('session_id');
            $table->timestamp('clicked_at');
            $table->timestamps();
            
            $table->index(['tracking_link_id', 'clicked_at']);
            $table->index(['funnel_id', 'clicked_at']);
            $table->index('session_id');
            $table->index('ip_address');
            $table->index('clicked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link_clicks');
    }
};
