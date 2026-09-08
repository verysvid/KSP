<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ApplicationSetting extends Model
{
    public const CACHE_KEY = 'application_settings.current';

    protected $fillable = [
        'system_name',
        'title_1',
        'title_2',
        'abbreviation',
        'description',
        'copyright',
        'logo_path',
        'icon_path',
    ];

    public static function defaults(): self
    {
        return new self([
            'system_name' => 'Koperasi Simpan Pinjam',
            'title_1' => 'Koperasi Pegawai BPPT Kabupaten Bekasi',
            'title_2' => 'MAHABAH BERSAMA SEJAHTERA',
            'abbreviation' => 'KSP',
            'description' => 'Sistem Informasi Pengelolaan Koperasi Simpan Pinjam',
            'copyright' => '© 2026. All rights reserved.',
        ]);
    }

    public static function current(): self
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return static::query()->first() ?? static::defaults();
        });
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path
            ? Storage::disk('public')->url($this->logo_path)
            : null;
    }

    public function getIconUrlAttribute(): ?string
    {
        return $this->icon_path
            ? Storage::disk('public')->url($this->icon_path)
            : null;
    }
}
