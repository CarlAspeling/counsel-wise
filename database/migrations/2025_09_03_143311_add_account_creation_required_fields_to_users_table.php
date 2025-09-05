<?php

use App\Enums\AccountType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add required fields for user account creation and management.
     * Adds surname, HPCSA registration details, profile picture,
     * account type classification, and soft delete functionality.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('surname');
            $table->string('hpcsa_number')->nullable();
            $table->timestamp('hpcsa_verified_at')->nullable();
            $table->string('profile_picture')->nullable();
            $table->enum('account_type', array_column(AccountType::cases(), 'value'));
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'surname',
                'hpcsa_number',
                'hpcsa_verified_at',
                'profile_picture',
                'account_type',
                'deleted_at',
            ]);
        });
    }
};
