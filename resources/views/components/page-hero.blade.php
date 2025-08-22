@props([
    'title' => '',
    'description' => '',
    'breadcrumbs' => [],
])

<section class="bg-white dark:bg-gray-900 py-20 relative">
    <!-- Фоновый паттерн -->
    <div class="absolute inset-0 bg-gray-50 dark:bg-gray-800 opacity-50"></div>

    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <!-- Хлебные крошки -->
        @if (!empty($breadcrumbs))
            <nav class="mb-6 text-center" aria-label="Хлебные крошки">
                <ol class="flex items-center justify-center space-x-2 text-sm">
                    <li>
                        <a href="{{ route('home') }}"
                            class="flex items-center text-gray-500 dark:text-gray-400 hover:text-accent dark:hover:text-accent-light transition-colors duration-300">
                            <i class="mdi mdi-home mr-1"></i>
                            Главная
                        </a>
                    </li>
                    @foreach ($breadcrumbs as $index => $crumb)
                        <li class="flex items-center">
                            <i class="mdi mdi-chevron-right text-gray-400 mx-2"></i>
                            @if ($index === count($breadcrumbs) - 1)
                                <span class="text-gray-900 dark:text-white font-semibold">{{ $crumb['name'] }}</span>
                            @else
                                <a href="{{ $crumb['href'] }}"
                                    class="text-gray-500 dark:text-gray-400 hover:text-accent dark:hover:text-accent-light transition-colors duration-300">
                                    {{ $crumb['name'] }}
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        <!-- Заголовок и описание -->
        <div class="text-center">
            @if ($title)
                <h1 class="text-4xl md:text-5xl font-black mb-6 text-gray-900 dark:text-white">
                    {!! $title !!}
                </h1>
            @endif

            @if ($description)
                <p class="text-xl text-gray-700 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed">
                    {{ $description }}
                </p>
            @endif
        </div>
    </div>
</section>
