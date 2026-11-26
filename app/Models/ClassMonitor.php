<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, Pivot};

/**
 * @property int $class_group_id
 * @property int $user_id
 * @property int $appointed_by
 * @property CarbonImmutable $appointed_at
 */
class ClassMonitor extends Pivot
{
    protected $table = 'class_monitor';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'appointed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function monitor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
