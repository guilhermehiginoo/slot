<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\{Fillable, Scope};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $monitor_id
 * @property int $class_group_id
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $ends_at
 * @property CarbonImmutable|null $cancelled_at
 * @property string $location
 * @property int $capacity
 */
#[Fillable(['class_group_id', 'monitor_id', 'starts_at', 'ends_at', 'location', 'capacity', 'cancelled_at'])]
class Slot extends Model
{
    public const string TIMEZONE = 'America/Fortaleza';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'starts_at'    => 'immutable_datetime',
            'ends_at'      => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ClassGroup, $this> */
    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class);
    }

    /** @param Builder<Slot> $query */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereNull('cancelled_at');
    }
}
