<?php

namespace App\Models;

use Database\Factories\ClassGroupFactory;
use Illuminate\Database\Eloquent\Attributes\{Fillable, Scope};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany};
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $course_id
 * @property string $semester
 * @property string $number
 * @property string $schedule
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['course_id', 'semester', 'number', 'schedule'])]
class ClassGroup extends Model
{
    /** @use HasFactory<ClassGroupFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function professors(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @param  Builder<ClassGroup>  $query
     */
    #[Scope]
    protected function currentSemester(Builder $query): void
    {
        $query->where('semester', config('slot.current_semester'));
    }
}
