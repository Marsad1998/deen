<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $table = 'store_settings';

    protected $fillable = [
        'setting_group',
        'setting_key',
        'setting_value',
    ];

    /**
     * Helper to get a setting value by key with optional default.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = static::where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Helper to set a setting value idempotently.
     */
    public static function set(string $key, ?string $value, string $group = 'general'): static
    {
        return static::updateOrCreate(
            ['setting_key' => $key],
            ['setting_group' => $group, 'setting_value' => $value]
        );
    }
}
