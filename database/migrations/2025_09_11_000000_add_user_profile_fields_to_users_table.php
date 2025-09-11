<?php

use App\Enums\AccountStatus;
use App\Enums\Gender;
use App\Enums\Language;
use App\Enums\SouthAfricanProvince;
use App\Enums\ThemePreference;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add user profile fields for enhanced user management.
     * Includes login tracking, preferences, consent tracking,
     * demographics, and notification settings.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable();
            $table->enum('account_status', array_column(AccountStatus::cases(), 'value'))->default(AccountStatus::Pending->value);
            $table->enum('theme_preference', array_column(ThemePreference::cases(), 'value'))->nullable();
            $table->timestamp('data_privacy_consent')->nullable();
            $table->timestamp('terms_accepted_at')->nullable();
            $table->json('notification_preferences')->default('{}');
            $table->string('phone_number')->nullable();
            $table->enum('gender', array_column(Gender::cases(), 'value'))->nullable();
            $table->enum('language', array_column(Language::cases(), 'value'))->default(Language::English->value);
            $table->enum('region', array_column(SouthAfricanProvince::cases(), 'value'))->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'last_login_at',
                'account_status',
                'theme_preference',
                'data_privacy_consent',
                'terms_accepted_at',
                'notification_preferences',
                'phone_number',
                'gender',
                'language',
                'region',
            ]);
        });
    }
};
