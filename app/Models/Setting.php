<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public const CACHE_KEY = 'portal.settings';

    protected $fillable = ['group', 'key', 'value', 'type', 'label', 'hint', 'options', 'sort_order'];

    protected $casts = [
        'options' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * All settings as a flat key => value map, cached for the request cycle.
     */
    public static function map(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return self::query()->pluck('value', 'key')->all();
        });
    }

    public static function value(string $key, mixed $default = null): mixed
    {
        $value = self::map()[$key] ?? null;

        return filled($value) ? $value : $default;
    }

    public static function put(string $key, mixed $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        self::flush();
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::flush());
        static::deleted(fn () => self::flush());
    }
}
