<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Adds a public-facing ULID so internal auto-increment IDs are never
 * exposed in URLs (spec §15).
 */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
