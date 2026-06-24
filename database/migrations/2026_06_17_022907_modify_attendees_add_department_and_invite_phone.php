<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            // Tambah kolom invite_phone untuk menyimpan nomor WA saat invite
            $table->string('invite_phone')->nullable()->after('email');
            // Tambah department_id (gantikan company)
            $table->foreignId('department_id')->nullable()->after('company')->constrained('departments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['invite_phone', 'department_id']);
        });
    }
};
