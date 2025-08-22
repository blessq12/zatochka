<?php

namespace App\Console\Commands;

use App\Models\Review;
use Illuminate\Console\Command;

class ModerateReviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviews:moderate
                            {--auto-approve : Автоматически одобрять отзывы с рейтингом 4-5}
                            {--auto-reject : Автоматически отклонять отзывы с рейтингом 1-2}
                            {--type= : Тип отзывов для модерации (feedback/testimonial)}
                            {--source= : Источник отзывов для модерации}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Модерация отзывов на основе заданных правил';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = Review::pending();

        // Фильтрация по типу
        if ($type = $this->option('type')) {
            $query->ofType($type);
        }

        // Фильтрация по источнику
        if ($source = $this->option('source')) {
            $query->ofSource($source);
        }

        $pendingReviews = $query->get();

        if ($pendingReviews->isEmpty()) {
            $this->info('Нет отзывов для модерации.');
            return 0;
        }

        $this->info("Найдено {$pendingReviews->count()} отзывов для модерации.");

        $approved = 0;
        $rejected = 0;
        $skipped = 0;

        foreach ($pendingReviews as $review) {
            $action = $this->determineAction($review);

            switch ($action) {
                case 'approve':
                    $review->approve();
                    $approved++;
                    $this->line("✓ Одобрен отзыв #{$review->id} (рейтинг: {$review->rating})");
                    break;

                case 'reject':
                    $review->reject();
                    $rejected++;
                    $this->line("✗ Отклонен отзыв #{$review->id} (рейтинг: {$review->rating})");
                    break;

                case 'skip':
                    $skipped++;
                    $this->line("- Пропущен отзыв #{$review->id} (требует ручной модерации)");
                    break;
            }
        }

        $this->newLine();
        $this->info("Модерация завершена:");
        $this->line("  Одобрено: {$approved}");
        $this->line("  Отклонено: {$rejected}");
        $this->line("  Пропущено: {$skipped}");

        return 0;
    }

    /**
     * Определить действие для отзыва
     */
    private function determineAction(Review $review): string
    {
        // Если нет рейтинга, пропускаем
        if (!$review->rating) {
            return 'skip';
        }

        // Автоматическое одобрение отзывов с высоким рейтингом
        if ($this->option('auto-approve') && $review->rating >= 4) {
            return 'approve';
        }

        // Автоматическое отклонение отзывов с низким рейтингом
        if ($this->option('auto-reject') && $review->rating <= 2) {
            return 'reject';
        }

        // Проверяем на спам/нецензурную лексику
        if ($this->containsSpam($review->comment)) {
            return 'reject';
        }

        // Проверяем на положительные ключевые слова
        if ($this->containsPositiveKeywords($review->comment) && $review->rating >= 3) {
            return 'approve';
        }

        return 'skip';
    }

    /**
     * Проверка на спам
     */
    private function containsSpam(string $comment): bool
    {
        $spamKeywords = [
            'купить',
            'продать',
            'заработок',
            'деньги',
            'казино',
            'лотерея',
            'кредит',
            'займ',
            'виагра',
            'секс',
            'порно',
            'xxx'
        ];

        $comment = mb_strtolower($comment);

        foreach ($spamKeywords as $keyword) {
            if (str_contains($comment, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверка на положительные ключевые слова
     */
    private function containsPositiveKeywords(string $comment): bool
    {
        $positiveKeywords = [
            'отлично',
            'хорошо',
            'качественно',
            'быстро',
            'доволен',
            'спасибо',
            'рекомендую',
            'супер',
            'класс',
            'лучший',
            'профессионально'
        ];

        $comment = mb_strtolower($comment);

        foreach ($positiveKeywords as $keyword) {
            if (str_contains($comment, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
