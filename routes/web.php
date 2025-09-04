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
    Route::get('/set-main-branch', [TestCompanyController::class, 'testSetBranchAsMain']);
    Route::get('/change-main-branch', [TestCompanyController::class, 'testChangeMainBranch']);
    Route::get('/activate-deactivate', [TestCompanyController::class, 'testActivateDeactivateCompany']);
    Route::get('/update', [TestCompanyController::class, 'testUpdateCompany']);
    Route::get('/find-by-inn', [TestCompanyController::class, 'testFindCompanyByInn']);
    Route::get('/exists', [TestCompanyController::class, 'testCompanyExists']);
    Route::get('/active', [TestCompanyController::class, 'testGetActiveCompanies']);
    Route::get('/delete', [TestCompanyController::class, 'testDeleteCompany']);
    Route::get('/delete-branch', [TestCompanyController::class, 'testDeleteBranch']);
    Route::get('/branch-company-relationship', [TestCompanyController::class, 'testBranchCompanyRelationship']);
});
