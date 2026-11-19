<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // one row: how long a sign-in may sit idle / last at most (CheckSessionActivity)
        Schema::create('session_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('idle_timeout_minutes')->default(30);
            $table->unsignedInteger('session_lifetime_minutes')->default(720);
            $table->timestamps();
        });
        DB::table('session_settings')->insert(['idle_timeout_minutes' => 30, 'session_lifetime_minutes' => 720, 'created_at' => now(), 'updated_at' => now()]);

        // "Require password change at next sign-in" (RequirePasswordChange)
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_settings');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('must_change_password'));
    }
};
