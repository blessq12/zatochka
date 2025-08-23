<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegram_chat_id',
        'client_id',
        'message_id',
        'direction',
        'type',
        'content',
        'media_data',
        'metadata',
        'sent_at',
    ];

    protected $casts = [
        'media_data' => 'array',
        'metadata' => 'array',
        'sent_at' => 'datetime',
    ];

    /**
     * Отношение к чату
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(TelegramChat::class, 'telegram_chat_id');
    }

    /**
     * Отношение к клиенту
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Проверить, является ли сообщение входящим
     */
    public function isIncoming(): bool
    {
        return $this->direction === 'incoming';
    }

    /**
     * Проверить, является ли сообщение исходящим
     */
    public function isOutgoing(): bool
    {
        return $this->direction === 'outgoing';
    }

    /**
     * Проверить, является ли сообщение командой
     */
    public function isCommand(): bool
    {
        return $this->type === 'command';
    }

    /**
     * Получить короткий текст сообщения (для предпросмотра)
     */
    public function getShortContentAttribute(): string
    {
        if (!$this->content) {
            return match ($this->type) {
                'photo' => '📷 Фото',
                'document' => '📄 Документ',
                'audio' => '🎵 Аудио',
                'video' => '🎬 Видео',
                'voice' => '🎤 Голосовое сообщение',
                'sticker' => '😀 Стикер',
                'command' => '⚡ Команда',
                default => '📎 Медиа файл'
            };
        }

        return mb_strlen($this->content) > 100
            ? mb_substr($this->content, 0, 100) . '...'
            : $this->content;
    }

    /**
     * Создать запись о входящем сообщении
     */
    public static function createIncoming(array $data): self
    {
        return static::create(array_merge($data, [
            'direction' => 'incoming',
            'sent_at' => now(),
        ]));
    }

    /**
     * Создать запись об исходящем сообщении
     */
    public static function createOutgoing(array $data): self
    {
        return static::create(array_merge($data, [
            'direction' => 'outgoing',
            'sent_at' => now(),
        ]));
    }

    /**
     * Получить историю сообщений для чата
     */
    public static function getChatHistory(int $chatId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('telegram_chat_id', $chatId)
            ->orderBy('sent_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse();
    }

    /**
     * Получить историю сообщений для клиента
     */
    public static function getClientHistory(int $clientId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('client_id', $clientId)
            ->orderBy('sent_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse();
    }

    /**
     * Получить последнее сообщение для чата
     */
    public static function getLastMessage(int $chatId): ?self
    {
        return static::where('telegram_chat_id', $chatId)
            ->orderBy('sent_at', 'desc')
            ->first();
    }
}
