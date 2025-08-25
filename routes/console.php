<?php

use Illuminate\Support\Facades\Schedule;

// fetch reviews from api services every day
Schedule::command('reviews:fetch')->daily();
Schedule::command('queue:check-n-restart')->everyFiveMinutes();
