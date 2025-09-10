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
        // Check if slug column doesn't exist before adding it
        if (!Schema::hasColumn('funnels', 'slug')) {
            Schema::table('funnels', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }
        
        // Generate slugs for existing funnels that don't have one
        $funnels = \App\Models\Funnel::whereNull('slug')->get();
        foreach ($funnels as $funnel) {
            $slug = \Illuminate\Support\Str::slug($funnel->name);
            // Ensure unique slug
            $originalSlug = $slug;
            $counter = 1;
            while (\App\Models\Funnel::where('slug', $slug)->where('id', '!=', $funnel->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            $funnel->update(['slug' => $slug]);
        }
        
        // Try to make slug unique - if it fails, that's ok as slugs are already populated
        try {
            Schema::table('funnels', function (Blueprint $table) {
                $table->string('slug')->unique()->change();
            });
        } catch (\Exception $e) {
            // Ignore if unique constraint already exists
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funnels', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
