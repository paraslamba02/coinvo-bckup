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
            $table->unsignedBigInteger('tracking_link_id')->nullable()->after('platform');
            $table->string('traffic_source')->nullable()->after('tracking_link_id');
            $table->string('session_id')->nullable()->after('traffic_source');

            $table->foreign('tracking_link_id')->references('id')->on('tracking_links')->onDelete('set null');
            $table->index(['traffic_source', 'invite_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_users', function (Blueprint $table) {
            $table->dropForeign(['tracking_link_id']);
            $table->dropIndex(['traffic_source', 'invite_code']);
            $table->dropColumn(['tracking_link_id', 'traffic_source', 'session_id']);
        });
    }
};
