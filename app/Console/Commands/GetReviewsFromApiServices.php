<?php

namespace App\Console\Commands;

use App\Contracts\Reviews\IReviewFactory;
use Illuminate\Console\Command;

class GetReviewsFromApiServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviews:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получаем отзывы с сервисов API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $reviewFactory = app(IReviewFactory::class);
        $services = $reviewFactory->callAllServices();

        foreach ($services as $service) {
            $reviews = $service->getReviews();
            $this->info('Получены отзывы с сервиса ' . get_class($service));
        }
    }
}
