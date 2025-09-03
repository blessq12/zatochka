<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestEventController;
use App\Http\Controllers\TestCompanyController;

Route::get('/', function () {
    return view('welcome');
});

// Маршруты для тестирования доменных событий
Route::prefix('test/events')->group(function () {
    Route::get('/user-registered', [TestEventController::class, 'testUserRegisteredEvent']);
    Route::get('/subscription', [TestEventController::class, 'testEventSubscription']);
    Route::get('/stats', [TestEventController::class, 'getEventBusStats']);
    Route::get('/all', [TestEventController::class, 'getAllEvents']);
});

// Маршруты для тестирования домена компании
Route::prefix('test/company')->group(function () {
    Route::get('/create', [TestCompanyController::class, 'testCreateCompany']);
    Route::get('/create-branch', [TestCompanyController::class, 'testCreateBranch']);
    Route::get('/list', [TestCompanyController::class, 'testGetCompany']);
    Route::get('/branches', [TestCompanyController::class, 'testGetBranches']);
    Route::get('/schedule', [TestCompanyController::class, 'testWorkingSchedule']);
});
