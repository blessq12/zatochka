@php
    /** @var string $title */
@endphp
<section class="bg-[#C20A6C] text-white py-12 sm:py-16">
    <div class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 text-center space-y-6">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-jost-bold tracking-wide">
            {{ $title }}
        </h1>
        @isset($slot)
            {{ $slot }}
        @endisset
        {{ $content ?? '' }}
    </div>
</section>
