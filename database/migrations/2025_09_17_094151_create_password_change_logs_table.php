<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create password change audit log table for security monitoring.
     *
     * This table tracks all password change attempts (successful and failed)
     * for security audit purposes. Stores essential metadata including:
     * - User identification and request metadata (IP, user agent)
     * - Attempt timing and success status
     * - Failure reasons for security analysis
     *
     * Supports security compliance requirements and helps detect:
     * - Brute force password change attempts
     * - Suspicious activity patterns
     * - Unauthorized access attempts
     */
    public function up(): void
    {
        Schema::create('password_change_logs', function (Blueprint $table) {
            $table->id();

            // User relationship - cascade delete to clean up logs when user is deleted
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Request metadata for security analysis
            $table->string('ip_address', 45)->nullable(); // IPv6 compatible (45 chars max)
            $table->text('user_agent')->nullable(); // Browser/client information

            // Audit trail timing - when the attempt occurred
            $table->timestamp('attempted_at');

            // Attempt outcome tracking
            $table->boolean('success')->default(false); // True for successful changes
            $table->string('failure_reason')->nullable(); // e.g., 'invalid_current_password', 'validation_failed'

            $table->timestamps();

            // Performance indexes for common queries
            $table->index(['user_id', 'attempted_at']); // User's password change history
            $table->index('success'); // Filter by success/failure for security analysis
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_change_logs');
    }
};
