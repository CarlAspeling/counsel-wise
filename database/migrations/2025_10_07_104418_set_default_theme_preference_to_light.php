<?php

use App\Enums\ThemePreference;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Set default theme preference to light for all users.
 *
 * Updates the theme_preference column to default to 'light' and backfills
 * any existing NULL values to ensure all users have a theme preference set.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing users with NULL theme_preference to 'light'
        DB::table('users')
            ->whereNull('theme_preference')
            ->update(['theme_preference' => ThemePreference::Light->value]);

        // Set default value using raw SQL (PostgreSQL compatible)
        DB::statement(
            "ALTER TABLE users ALTER COLUMN theme_preference SET DEFAULT '".ThemePreference::Light->value."'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove default value using raw SQL (PostgreSQL compatible)
        DB::statement('ALTER TABLE users ALTER COLUMN theme_preference DROP DEFAULT');
    }
};
