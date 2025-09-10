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
        Schema::table('affiliate_users', function (Blueprint $table) {
            // Remove KYC field and add funnel tracking
            $table->dropColumn('kyc_status');
            
            // Add funnel step tracking
            $table->string('funnel_step')->default('step1_signup'); // step1_signup, step2_deposit, step3_reward_claimed
            $table->timestamp('step1_completed_at')->nullable(); // When they signed up
            $table->timestamp('step2_completed_at')->nullable(); // When they deposited
            $table->timestamp('step3_completed_at')->nullable(); // When they claimed reward
            $table->decimal('deposit_amount', 10, 2)->nullable(); // Deposit amount
            $table->string('reward_status')->default('pending'); // pending, claimed, rejected
            $table->text('notes')->nullable(); // Admin notes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_users', function (Blueprint $table) {
            // Restore KYC field
            $table->boolean('kyc_status')->default(false);
            
            // Remove funnel tracking fields
            $table->dropColumn([
                'funnel_step',
                'step1_completed_at',
                'step2_completed_at', 
                'step3_completed_at',
                'deposit_amount',
                'reward_status',
                'notes'
            ]);
        });
    }
};
