<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('class_group_user', function (Blueprint $table) {
            $table->foreignId('class_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();

            $table->primary(['class_group_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_group_user');
    }
};
