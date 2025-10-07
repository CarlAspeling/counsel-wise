<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remove unused profile_picture column from users table.
 *
 * The application uses Spatie Media Library to manage profile pictures,
 * storing them in the 'media' table with full metadata support and
 * automatic image conversions. The profile_picture column in the users
 * table is no longer used and can be safely removed.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_picture');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_picture')->nullable()->after('account_type');
        });
    }
};
