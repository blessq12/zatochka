<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MainController extends Controller
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        return view('pages.index');
    }

    /**
     * Страница заточки
     */
    public function sharpening(): View
    {
        return view('pages.sharpening');
    }

    /**
     * Страница ремонта
     */
    public function repair(): View
    {
        return view('pages.repair');
    }

    /**
     * Страница доставки
     */
    public function delivery(): View
    {
        return view('pages.delivery');
    }

    /**
     * Страница контактов
     */
    public function contacts(): View
    {
        return view('pages.contacts');
    }

    /**
     * Политика конфиденциальности
     */
    public function privacyPolicy(): View
    {
        return view('pages.privacy-policy');
    }

    /**
     * Условия использования
     */
    public function termsOfService(): View
    {
        return view('pages.terms-of-service');
    }

    /**
     * Страница помощи
     */
    public function help(): View
    {
        return view('pages.help');
    }
}
