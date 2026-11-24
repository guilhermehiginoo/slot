<?php

namespace App\Models;

use App\Enums\MonitorApplicationStatus;
use Database\Factories\MonitorApplicationFactory;
use Illuminate\Database\Eloquent\Attributes\{Fillable, Scope};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $class_group_id
 * @property int $user_id
 * @property string|null $message
 * @property MonitorApplicationStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['class_group_id', 'user_id', 'message', 'status'])]
class MonitorApplication extends Model
{
    /** @use HasFactory<MonitorApplicationFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => MonitorApplicationStatus::Pending->value,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => MonitorApplicationStatus::class,
        ];
    }

    /**
     * @return BelongsTo<ClassGroup, $this>
     */
    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @param  Builder<MonitorApplication>  $query
     */
    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->where('status', MonitorApplicationStatus::Pending);
    }
}
