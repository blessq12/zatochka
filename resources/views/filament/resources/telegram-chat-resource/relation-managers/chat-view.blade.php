<div class="chat-container bg-gray-50 dark:bg-gray-900 rounded-lg p-3" style="max-height: 500px; height: 500px;">
    <div class="chat-messages space-y-2 overflow-y-auto" style="height: 100%;">
        @foreach ($messages as $message)
            @php
                $isIncoming = $message->direction === 'incoming';
                $content =
                    $message->content ?:
                    match ($message->type) {
                        'photo' => '📷 Фото',
                        'document' => '📄 Документ',
                        'audio' => '🎵 Аудио',
                        'video' => '🎬 Видео',
                        'voice' => '🎤 Голосовое сообщение',
                        'sticker' => '😀 Стикер',
                        'command' => '⚡ Команда',
                        default => '📎 Медиа файл',
                    };
                $time = $message->sent_at->format('H:i');
            @endphp

            <div class="flex {{ $isIncoming ? 'justify-start' : 'justify-end' }}">
                <div class="max-w-xs">
                    <div class="chat-bubble {{ $isIncoming ? 'chat-bubble-incoming' : 'chat-bubble-outgoing' }}">
                        <div class="chat-bubble-content">
                            {!! nl2br(e($content)) !!}
                        </div>
                        <div class="chat-bubble-time">
                            {{ $time }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
    .chat-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .dark .chat-container {
        background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
    }

    .chat-bubble {
        display: inline-block;
        max-width: 100%;
        padding: 8px 12px;
        border-radius: 16px;
        position: relative;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        animation: messageSlideIn 0.3s ease-out;
    }

    .chat-bubble-incoming {
        background: #ffffff;
        color: #1a202c;
        border-radius: 16px 16px 16px 4px;
        margin-right: auto;
    }

    .dark .chat-bubble-incoming {
        background: #2d3748;
        color: #e2e8f0;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    .chat-bubble-outgoing {
        background: #0084ff;
        color: #ffffff;
        border-radius: 16px 16px 4px 16px;
        margin-left: auto;
    }

    .chat-bubble-content {
        font-size: 13px;
        line-height: 1.3;
        white-space: pre-wrap;
        word-wrap: break-word;
        margin-bottom: 2px;
    }

    .chat-bubble-time {
        font-size: 10px;
        opacity: 0.7;
        text-align: right;
        margin-top: 1px;
    }

    .chat-bubble-incoming .chat-bubble-time {
        color: #718096;
    }

    .dark .chat-bubble-incoming .chat-bubble-time {
        color: #a0aec0;
    }

    .chat-bubble-outgoing .chat-bubble-time {
        color: rgba(255, 255, 255, 0.8);
    }

    @keyframes messageSlideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Улучшенные стили для пузырей */
    .chat-bubble::before {
        content: '';
        position: absolute;
        bottom: 0;
        width: 0;
        height: 0;
    }

    .chat-bubble-incoming::before {
        left: -8px;
        border: 8px solid transparent;
        border-bottom-color: #ffffff;
        border-left-color: #ffffff;
    }

    .dark .chat-bubble-incoming::before {
        border-bottom-color: #2d3748;
        border-left-color: #2d3748;
    }

    .chat-bubble-outgoing::before {
        right: -8px;
        border: 8px solid transparent;
        border-bottom-color: #0084ff;
        border-right-color: #0084ff;
    }
</style>
