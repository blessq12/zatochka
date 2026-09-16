@extends('layouts.site')

@section('content')
<div class="min-h-screen flex items-center justify-center px-8 py-20">
    <div class="text-center space-y-6">
        <h1 class="text-3xl font-jost-bold text-[#C20A6C]">404</h1>
        <p class="text-lg font-jost-regular text-dark-gray-500 dark:text-gray-300">
            Страница не найдена
        </p>
        <a href="{{ url('/') }}" class="inline-block bg-dark-blue-500 text-white px-8 py-3 font-jost-bold">
            На главную
        </a>
    </div>
</div>
@endsection
