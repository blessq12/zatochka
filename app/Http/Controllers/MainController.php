<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class MainController extends Controller
{
    /**
     * SPA layout - возвращает базовый layout для всех маршрутов
     */
    public function index(): View
    {
        return view('components.layouts.app');
    }
}
