<?php

namespace App\Models;

use Database\Factories\ClassGroupFactory;
use Illuminate\Database\Eloquent\Attributes\{Fillable, Scope};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany};
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $course_id
 * @property string $semester
 * @property string $number
 * @property string $schedule
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int|null $monitors_count
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
     * @return BelongsToMany<User, $this, ClassMonitor>
     */
    public function monitors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_monitor')
            ->using(ClassMonitor::class)
            ->withPivot(['appointed_by', 'appointed_at']);
    }

    /**
     * @return HasMany<MonitorInvitation, $this>
     */
    public function monitorInvitations(): HasMany
    {
        return $this->hasMany(MonitorInvitation::class);
    }

    /**
     * @return HasMany<MonitorApplication, $this>
     */
    public function monitorApplications(): HasMany
    {
        return $this->hasMany(MonitorApplication::class);
    }

    /**
     * @param  Builder<ClassGroup>  $query
     */
    #[Scope]
    protected function currentSemester(Builder $query): void
    {
        $query->where('semester', config('slot.current_semester'));
    }

    /**
     * @param  Builder<ClassGroup>  $query
     */
    #[Scope]
    protected function taughtBy(Builder $query, User $professor): void
    {
        $query->whereRelation('professors', 'users.id', $professor->id);
    }
}
