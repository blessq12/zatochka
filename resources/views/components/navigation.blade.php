@props(['navigation' => []])

<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('home') }}">
                        <img class="h-8 w-auto" src="/logo.png" alt="{{ config('app.name') }}">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    @foreach ($navigation as $item)
                        <a href="{{ $item['url'] }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->url() == $item['url'] ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                            @if (isset($item['icon']))
                                <i class="mdi mdi-{{ $item['icon'] }} mr-2"></i>
                            @endif
                            {{ $item['title'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Mobile menu button -->
            <div class="flex items-center sm:hidden">
                <button type="button"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
                    x-data="{ open: false }" @click="open = !open" aria-expanded="false">
                    <span class="sr-only">Открыть меню</span>
                    <i class="mdi mdi-menu h-6 w-6" x-show="!open"></i>
                    <i class="mdi mdi-close h-6 w-6" x-show="open" style="display: none;"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div class="sm:hidden" x-data="{ open: false }" x-show="open" style="display: none;">
        <div class="pt-2 pb-3 space-y-1">
            @foreach ($navigation as $item)
                <a href="{{ $item['url'] }}"
                    class="block pl-3 pr-4 py-2 border-l-4 {{ request()->url() == $item['url'] ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }}">
                    @if (isset($item['icon']))
                        <i class="mdi mdi-{{ $item['icon'] }} mr-2"></i>
                    @endif
                    {{ $item['title'] }}
                </a>
            @endforeach
        </div>
    </div>
</nav>
