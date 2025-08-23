<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'chat_id',
        'first_name',
        'last_name',
        'is_active',
        'last_activity_at',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_activity_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Отношение к сообщениям
     */
    public function messages(): HasMany
    {
        return $this->hasMany(TelegramMessage::class, 'telegram_chat_id');
    }

    /**
     * Отношение к клиенту (через username)
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'username', 'telegram');
    }

    /**
     * Получить полное имя пользователя
     */
    public function getFullNameAttribute()
    {
        $parts = array_filter([$this->first_name, $this->last_name]);
        return implode(' ', $parts) ?: $this->username;
    }

    /**
     * Получить username с @
     */
    public function getUsernameWithAtAttribute()
    {
        return '@' . $this->username;
    }

    /**
     * Обновить время последней активности
     */
    public function updateLastActivity()
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Деактивировать чат
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Активировать чат
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Найти чат по username
     */
    public static function findByUsername(string $username)
    {
        return static::where('username', ltrim($username, '@'))->first();
    }

    /**
     * Найти чат по chat_id
     */
    public static function findByChatId(int $chatId)
    {
        return static::where('chat_id', $chatId)->first();
    }

    /**
     * Создать или обновить чат
     */
    public static function createOrUpdate(array $data)
    {
        return static::updateOrCreate(
            ['chat_id' => $data['chat_id']],
            $data
        );
    }

    /**
     * Получить последнее сообщение
     */
    public function getLastMessage()
    {
        return $this->messages()->orderBy('sent_at', 'desc')->first();
    }

    /**
     * Получить количество непрочитанных сообщений
     */
    public function getUnreadCount()
    {
        return $this->messages()
            ->where('direction', 'incoming')
            ->where('is_read', false)
            ->count();
    }
}
