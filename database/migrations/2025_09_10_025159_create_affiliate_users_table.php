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
        Schema::create('affiliate_users', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->string('platform');
            $table->timestamp('register_time')->nullable();
            $table->boolean('kyc_status')->default(false);
            $table->string('invite_code')->nullable();
            $table->timestamp('first_deposit_time')->nullable();
            $table->timestamp('first_trade_time')->nullable();
            $table->timestamp('last_deposit_time')->nullable();
            $table->timestamp('last_trade_time')->nullable();
            $table->timestamps();
            
            $table->index(['platform', 'invite_code']);
            $table->index('kyc_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_users');
    }
};
