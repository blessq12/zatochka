<?php

use App\Http\Controllers\Documents\LegalDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('documents/{type}', LegalDocumentController::class);
