<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function index()
    {
        return view('pages.index');
    }

    public function sharpening()
    {
        return view('pages.sharpening');
    }

    public function repair()
    {
        return view('pages.repair');
    }

    public function delivery()
    {
        return view('pages.delivery');
    }

    public function contacts()
    {
        return view('pages.contacts');
    }

    public function privacyPolicy()
    {
        return view('pages.privacy-policy');
    }

    public function termsOfService()
    {
        return view('pages.terms-of-service');
    }

    public function help()
    {
        return view('pages.help');
    }
}
