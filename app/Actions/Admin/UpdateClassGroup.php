<?php

namespace App\Actions\Admin;

use App\Models\ClassGroup;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateClassGroup
{
    /**
     * @param  array{course_id: int, semester: string, number: string, schedule: string, professors: array<int, int>}  $data
     */
    public function handle(ClassGroup $classGroup, array $data): ClassGroup
    {
        return DB::transaction(function () use ($classGroup, $data) {
            $classGroup->update(Arr::except($data, 'professors'));
            $classGroup->professors()->sync($data['professors']);

            return $classGroup;
        });
    }
}
