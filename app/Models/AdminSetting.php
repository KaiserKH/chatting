<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'updated_by',
    ];

    public static function bool(string $key, bool $default = false): bool
    {
        $value = static::query()->where('setting_key', $key)->value('setting_value');

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function setBool(string $key, bool $value, ?int $updatedBy = null): void
    {
        static::query()->updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $value ? '1' : '0',
                'updated_by' => $updatedBy,
            ]
        );
    }
}
