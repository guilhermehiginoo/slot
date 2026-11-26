<?php

use App\Models\Course;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('description', 500)->nullable();
            $table->string('search_name')->default('')->index();
        });

        DB::table('courses')->select(['id', 'name'])->chunkById(100, function ($courses) {
            foreach ($courses as $course) {
                DB::table('courses')
                    ->where('id', $course->id)
                    ->update(['search_name' => Course::normalize($course->name)]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['search_name']);
            $table->dropColumn(['description', 'search_name']);
        });
    }
};
