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
        Schema::create('attendees', function (Blueprint $table) {
            $table->id();
            $table->string('attendee_id')->nullable()->unique();
            $table->string('email')->unique();
            $table->string('invitation_token')->nullable()->unique();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('ticket_type')->nullable();
            $table->string('qr_token')->nullable()->unique();
            $table->enum('status', ['invited', 'registered', 'cancelled'])->default('invited');
            $table->enum('checkin_status', ['not_checked_in', 'checked_in'])->default('not_checked_in');
            $table->timestamp('checkin_time')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendees');
    }
};
