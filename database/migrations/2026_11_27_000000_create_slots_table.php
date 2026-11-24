<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('slots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('class_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('monitor_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('location');
            $table->unsignedInteger('capacity');
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['monitor_id', 'cancelled_at', 'starts_at']);
            $table->index(['class_group_id', 'cancelled_at', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slots');
    }
};
