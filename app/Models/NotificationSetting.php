<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class NotificationSetting extends Model
{
    protected $table    = 'notification_settings';
    protected $fillable = ['channel','key','value','is_secret','is_active'];
    protected $casts    = ['is_secret'=>'boolean','is_active'=>'boolean'];

    /** Kanal sozlamalarini array ko'rinishida olish */
    public static function getChannel(string $channel): array
    {
        return Cache::remember("notif_settings_{$channel}", 300, function() use ($channel) {
            return static::where('channel', $channel)->pluck('value', 'key')->toArray();
        });
    }

    /** Bitta sozlamani olish */
    public static function get(string $channel, string $key, string $default = ''): string
    {
        $settings = static::getChannel($channel);
        $raw = $settings[$key] ?? $default;
        // Secret qiymatni decrypt
        $record = static::where('channel', $channel)->where('key', $key)->where('is_secret', true)->first();
        if ($record && $raw) {
            try { return Crypt::decryptString($raw); } catch (\Exception $e) { return $raw; }
        }
        return $raw;
    }

    /** Sozlamalarni saqlash (secret - encrypt, oddiy - oddiy) */
    public static function setChannel(string $channel, array $data): void
    {
        foreach ($data as $key => $value) {
            $record = static::where('channel', $channel)->where('key', $key)->first();
            if (!$record) continue;
            // Secret bo'lsa encrypt, bo'sh kelsa avvalgisini qoldir
            if ($record->is_secret) {
                if ($value === '' || $value === null) continue;
                $value = Crypt::encryptString($value);
            }
            $record->update(['value' => $value]);
        }
        Cache::forget("notif_settings_{$channel}");
    }

    /** Qiymatni maskalangan ko'rinishda olish (UI uchun) */
    public function getMaskedValueAttribute(): string
    {
        if ($this->is_secret && $this->value) return '••••••••';
        return $this->value ?? '';
    }
}