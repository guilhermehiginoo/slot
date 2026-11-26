<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\{Fillable, Scope};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\{Carbon, Str};

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $search_name
 * @property CarbonImmutable|null $archived_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['code', 'name', 'description'])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    public static function normalize(string $text): string
    {
        return Str::lower(Str::ascii($text));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
        ];
    }

    /**
     * @return Attribute<string, string>
     */
    protected function name(): Attribute
    {
        return Attribute::set(fn (string $value) => [
            'name'        => $value,
            'search_name' => self::normalize($value),
        ]);
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * @param  Builder<Course>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereNull('archived_at');
    }

    /**
     * @param  Builder<Course>  $query
     */
    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        if ($term === null) {
            return;
        }

        $normalized = self::normalize($term);

        $query->where(fn (Builder $query) => $query
            ->whereLike('code', Str::upper($normalized) . '%')
            ->orWhereLike('search_name', "%{$normalized}%"));
    }
}
