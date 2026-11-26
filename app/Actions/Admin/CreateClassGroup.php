<?php

namespace App\Actions\Admin;

use App\Models\ClassGroup;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateClassGroup
{
    /**
     * @param  array{course_id: int, semester: string, number: string, schedule: string, professors: array<int, int>}  $data
     */
    public function handle(array $data): ClassGroup
    {
        return DB::transaction(function () use ($data) {
            $classGroup = ClassGroup::create(Arr::except($data, 'professors'));
            $classGroup->professors()->sync($data['professors']);

            return $classGroup;
        });
    }
}
