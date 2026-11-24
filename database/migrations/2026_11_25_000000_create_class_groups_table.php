<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('class_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained();
            $table->string('semester', 6)->index();
            $table->string('number', 3);
            $table->string('schedule', 50);
            $table->timestamps();

            $table->unique(['course_id', 'semester', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_groups');
    }
};
