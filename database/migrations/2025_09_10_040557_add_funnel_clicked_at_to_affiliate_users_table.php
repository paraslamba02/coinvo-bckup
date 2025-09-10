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
            $table->timestamp('funnel_clicked_at')->nullable()->after('register_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_users', function (Blueprint $table) {
            $table->dropColumn('funnel_clicked_at');
        });
    }
};
