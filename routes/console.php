<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


// schedule a command to run
Schedule::call(function () {
    \Log::info('Hello World');
})->everyFiveSeconds();
