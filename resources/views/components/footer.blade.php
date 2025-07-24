@props([
    'contacts' => [],
    'socials' => [],
    'copyright' => '',
])

<footer class="bg-gray-800">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8">
        <div class="xl:grid xl:grid-cols-3 xl:gap-8">
            <!-- Logo and Description -->
            <div class="space-y-8 xl:col-span-1">
                <img class="h-10" src="/logo.png" alt="{{ config('app.name') }}">
                <p class="text-gray-400 text-base">
                    Профессиональная заточка инструментов
                </p>
                <div class="flex space-x-6">
                    @foreach ($socials as $social)
                        <a href="{{ $social['url'] }}" class="text-gray-400 hover:text-gray-300">
                            <span class="sr-only">{{ $social['title'] }}</span>
                            <i class="mdi mdi-{{ $social['icon'] }} text-2xl"></i>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Contact Information -->
            <div class="mt-12 xl:mt-0 xl:col-span-2">
                <div class="grid grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">
                            Контакты
                        </h3>
                        <ul role="list" class="mt-4 space-y-4">
                            @foreach ($contacts as $contact)
                                <li>
                                    <a href="{{ $contact['url'] }}"
                                        class="text-base text-gray-300 hover:text-white flex items-center">
                                        <i class="mdi mdi-{{ $contact['icon'] }} mr-2"></i>
                                        {{ $contact['title'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @if ($copyright)
            <div class="mt-12 border-t border-gray-700 pt-8">
                <p class="text-base text-gray-400 xl:text-center">
                    {{ $copyright }}
                </p>
            </div>
        @endif
    </div>
</footer>
