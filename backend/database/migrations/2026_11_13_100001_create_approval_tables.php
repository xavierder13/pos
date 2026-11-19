<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Approval flows, set up in the SPA (Administration → Approval Flows): one active flow per record type
        Schema::create('approval_flows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('record_type', 64)->index();   // App\Support\RecordTypes key
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // each level's approver: one user, or anyone with a role
        Schema::create('approval_flow_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_flow_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('level');
            $table->string('approver_type', 10);   // user | role
            $table->unsignedBigInteger('approver_id');
            $table->timestamps();
            $table->unique(['approval_flow_id', 'level']);
        });

        // one submission of a record through a flow
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->morphs('approvable');
            $table->foreignId('approval_flow_id')->constrained()->restrictOnDelete();
            $table->string('status', 16)->default('pending')->index();   // pending | approved | rejected | cancelled
            $table->unsignedSmallInteger('current_level')->default(1);
            $table->unsignedSmallInteger('total_levels');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });

        // who did what (the request's timeline)
        Schema::create('approval_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('level')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 16);   // submitted | approved | rejected | cancelled
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_actions');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_flow_levels');
        Schema::dropIfExists('approval_flows');
    }
};
