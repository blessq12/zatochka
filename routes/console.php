<?php

use Illuminate\Support\Facades\Schedule;

// fetch reviews from api services every day
Schedule::command('reviews:fetch')->daily();
// Проверяем очереди каждые 5 минут вместо 15
Schedule::command('queue:check-n-restart')->everyFiveMinutes();
