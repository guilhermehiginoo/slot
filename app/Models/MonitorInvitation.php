<?php

namespace App\Models;

use App\Enums\MonitorInvitationStatus;
use Database\Factories\MonitorInvitationFactory;
use Illuminate\Database\Eloquent\Attributes\{Fillable, Scope};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $class_group_id
 * @property int $user_id
 * @property int $invited_by
 * @property MonitorInvitationStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['class_group_id', 'user_id', 'invited_by', 'status'])]
class MonitorInvitation extends Model
{
    /** @use HasFactory<MonitorInvitationFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => MonitorInvitationStatus::Pending->value,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => MonitorInvitationStatus::class,
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
     * @return BelongsTo<User, $this>
     */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * @param  Builder<MonitorInvitation>  $query
     */
    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->where('status', MonitorInvitationStatus::Pending);
    }
}
