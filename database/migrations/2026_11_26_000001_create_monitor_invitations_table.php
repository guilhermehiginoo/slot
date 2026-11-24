<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('monitor_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('invited_by')->constrained('users');
            $table->string('status', 10)->default('pending');
            $table->timestamps();

            $table->index(['class_group_id', 'status']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitor_invitations');
    }
};
