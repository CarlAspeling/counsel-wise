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
        Schema::create('security_event_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type'); // SecurityEventType enum value
            $table->string('severity'); // info, notice, warning, alert, critical
            $table->text('description');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('email')->nullable(); // For events without user (failed logins)
            $table->ipAddress('ip_address');
            $table->string('user_agent')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('metadata')->nullable(); // Additional event-specific data
            $table->string('session_id')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            // Indexes for performance
            $table->index(['event_type', 'occurred_at']);
            $table->index(['user_id', 'occurred_at']);
            $table->index(['ip_address', 'occurred_at']);
            $table->index(['severity', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_event_logs');
    }
};
