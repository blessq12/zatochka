@php
    $layout = $layout ?? 'stack';
    $sharpeningLabel = $sharpeningLabel ?? 'Заказать заточку';
    $repairLabel = $repairLabel ?? 'Заказать ремонт';
    $withDeliveryHint = $withDeliveryHint ?? false;
    $wrapClass = $layout === 'row'
        ? 'flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center'
        : 'flex flex-col gap-3';
    $btn = 'bg-[#C3006B] hover:bg-[#C3006B]/90 text-white px-8 py-4 sm:py-5 font-jost-bold text-base sm:text-lg transition-all duration-300 shadow-lg hover:shadow-xl text-center';
    $btn2 = 'bg-dark-blue-500 hover:bg-dark-blue-600 dark:bg-dark-blue-600 dark:hover:bg-dark-blue-700 text-white px-8 py-4 sm:py-5 font-jost-bold text-base sm:text-lg transition-all duration-300 shadow-lg hover:shadow-xl text-center';
@endphp
<div>
    @if($withDeliveryHint)
        <p class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300 text-center mb-4">
            Курьер заберёт заказ у вас и вернёт обратно после работы.
        </p>
    @endif
    <div class="{{ $wrapClass }}">
        <a href="{{ url('/sharpening#order') }}" class="{{ $btn }}">{{ $sharpeningLabel }}</a>
        <a href="{{ url('/repair#order') }}" class="{{ $btn2 }}">{{ $repairLabel }}</a>
    </div>
</div>
