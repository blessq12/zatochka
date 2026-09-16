@php
    /** @var \App\Http\ViewModels\PublicSite\PublicSiteViewModel $site */
@endphp
<footer class="bg-white/85 backdrop-blur-2xl border-t border-white/25 dark:bg-gray-800/85 dark:border-gray-700/25 mt-auto">
    <div class="container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 py-8 sm:py-10 lg:py-12">
        @if($site->phoneTel() !== '')
            <div class="flex justify-center mb-6">
                <a href="tel:{{ $site->phoneTel() }}"
                   class="text-xl sm:text-2xl font-jost-bold text-dark-blue-500 hover:text-blue-500 dark:text-blue-400">
                    {{ $site->phone() }}
                </a>
            </div>
        @endif

        @if(count($site->socialLinks()) > 0)
            <div class="flex justify-center items-center gap-4 mb-6 flex-wrap">
                @foreach($site->socialLinks() as $link)
                    <a href="{{ $link['url'] ?? '#' }}"
                       target="_blank"
                       rel="noopener"
                       class="text-sm font-jost-medium text-dark-blue-500 hover:text-blue-500 dark:text-blue-400">
                        {{ $link['name'] ?? '' }}
                    </a>
                @endforeach
            </div>
        @endif

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6 mb-6 flex-wrap">
            <a href="{{ url('/privacy-policy') }}" class="text-sm font-jost-medium text-dark-gray-500 hover:text-blue-500 dark:text-gray-300">
                Политика конфиденциальности
            </a>
            <span class="hidden sm:inline text-dark-gray-500 dark:text-gray-400">|</span>
            <a href="{{ url('/user-agreement') }}" class="text-sm font-jost-medium text-dark-gray-500 hover:text-blue-500 dark:text-gray-300">
                Пользовательское соглашение
            </a>
            <span class="hidden sm:inline text-dark-gray-500 dark:text-gray-400">|</span>
            <a href="{{ url('/usage-rules') }}" class="text-sm font-jost-medium text-dark-gray-500 hover:text-blue-500 dark:text-gray-300">
                Правила пользования
            </a>
        </div>

        @php($company = $site->company())
        @if(!empty($company['owner_name']))
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 text-xs sm:text-sm font-jost-regular text-gray-500 dark:text-gray-400 mb-4">
                <p>{{ $company['owner_name'] }}</p>
                @if(!empty($company['inn']))
                    <span class="hidden sm:inline">•</span>
                    <p>ИНН: {{ $company['inn'] }}</p>
                @endif
                @if(!empty($company['ogrn']))
                    <span class="hidden sm:inline">•</span>
                    <p>ОГРН: {{ $company['ogrn'] }}</p>
                @endif
            </div>
        @endif

        <div class="flex flex-col items-center justify-center gap-2 sm:gap-3 text-xs sm:text-sm font-jost-regular text-gray-500 dark:text-gray-400">
            @if(!empty($company['legal_address']))
                <p><span class="font-jost-medium">Юридический адрес:</span> {{ $company['legal_address'] }}</p>
            @endif
            @if(!empty($company['actual_address']))
                <p><span class="font-jost-medium">Фактический адрес:</span> {{ $company['actual_address'] }}</p>
            @endif
        </div>
    </div>
</footer>
