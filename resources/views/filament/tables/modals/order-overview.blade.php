@php
    use App\Filament\Resources\OrderResource;
    use App\Models\Order;

    /** @var \App\Models\Order $record */
    $paymentSummary = OrderResource::orderPaymentSummaryLabel($record);
    $statusLabel = Order::getAvailableStatuses()[$record->status] ?? $record->status;
    $typeLabel = Order::getAvailableTypes()[$record->service_type] ?? $record->service_type;
    $paymentKind = match ($record->order_payment_type) {
        Order::PAYMENT_TYPE_PAID => 'Платный',
        Order::PAYMENT_TYPE_WARRANTY => 'Гарантийный',
        default => $record->order_payment_type ?? '—',
    };
    $urgencyLabel = Order::getAvailableUrgencies()[$record->urgency] ?? $record->urgency;

    $fiPanel = 'rounded-xl bg-gray-50 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10';
    $fiInset = 'rounded-lg bg-gray-100 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10';
    $fiDivider = 'border-gray-200 dark:border-white/10';
@endphp

{{-- Панели в стиле Filament (fi-section): ring + gray-50 / dark:bg-gray-900, без белых карточек --}}
<div class="order-overview-modal -mx-1 space-y-5 text-sm text-gray-950 dark:text-white sm:-mx-2">
    {{-- 1. Шапка --}}
    <header class="{{ $fiPanel }} px-4 py-4 sm:px-5 sm:py-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0 flex-1">
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Заказ</p>
                <div class="mt-1 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                    <span class="truncate text-lg font-semibold tracking-tight text-gray-950 dark:text-white">
                        {{ $record->order_number ?? '—' }}
                    </span>
                    <span
                        class="inline-flex items-center rounded-md bg-primary-50 px-2 py-0.5 text-xs font-medium text-primary-700 ring-1 ring-inset ring-primary-600/15 dark:bg-primary-500/10 dark:text-primary-300 dark:ring-primary-400/25">
                        {{ $statusLabel }}
                    </span>
                </div>
                <div class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-xs text-gray-600 dark:text-gray-400">
                    <span class="inline-flex items-center gap-1.5">
                        <x-filament::icon icon="heroicon-o-calendar-days" class="h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500" />
                        {{ $record->created_at?->format('d.m.Y · H:i') ?? '—' }}
                    </span>
                    @if (filled($record->branch?->name))
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <x-filament::icon icon="heroicon-o-building-storefront" class="h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500" />
                            <span class="truncate">{{ $record->branch->name }}</span>
                        </span>
                    @endif
                </div>
            </div>
            <div class="{{ $fiInset }} shrink-0 px-4 py-3 text-end lg:min-w-[9rem]">
                <p class="text-xs text-gray-500 dark:text-gray-400">Итого по заказу</p>
                <p class="mt-0.5 text-xl font-semibold tabular-nums tracking-tight text-gray-950 dark:text-white">
                    {{ number_format((float) $record->calculated_price, 2, ',', ' ') }}
                    <span class="text-base font-normal text-gray-500 dark:text-gray-400">₽</span>
                </p>
            </div>
        </div>
    </header>

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="{{ $fiPanel }} p-4 sm:p-5">
            <h2 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">Тип и условия</h2>
            <p class="mt-0.5 overflow-hidden break-words text-sm text-gray-500 dark:text-gray-400">Как оформлен заказ и насколько он срочный</p>
            <div class="mt-4 flex items-center gap-3 border-b {{ $fiDivider }} pb-4">
                @include('filament.tables.columns.order-listing-icons', ['record' => $record])
            </div>
            <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                <div class="{{ $fiInset }} px-3 py-2.5">
                    <dt class="text-xs text-gray-500 dark:text-gray-400">Тип услуги</dt>
                    <dd class="mt-0.5 font-medium text-gray-950 dark:text-white">{{ $typeLabel }}</dd>
                </div>
                <div class="{{ $fiInset }} px-3 py-2.5">
                    <dt class="text-xs text-gray-500 dark:text-gray-400">Вид</dt>
                    <dd class="mt-0.5 font-medium text-gray-950 dark:text-white">{{ $paymentKind }}</dd>
                </div>
                <div class="{{ $fiInset }} px-3 py-2.5">
                    <dt class="text-xs text-gray-500 dark:text-gray-400">Срочность</dt>
                    <dd class="mt-0.5 font-medium text-gray-950 dark:text-white">{{ $urgencyLabel }}</dd>
                </div>
                <div class="{{ $fiInset }} px-3 py-2.5">
                    <dt class="text-xs text-gray-500 dark:text-gray-400">Начисления</dt>
                    <dd class="mt-0.5 font-medium text-gray-950 dark:text-white">{{ $paymentSummary }}</dd>
                </div>
            </dl>
        </section>

        <section class="{{ $fiPanel }} p-4 sm:p-5">
            <h2 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">Клиент</h2>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Контакт для связи по заказу</p>
            <div class="mt-4 space-y-3">
                <p class="text-base font-medium leading-snug text-gray-950 dark:text-white">
                    {{ $record->client?->full_name ?? '—' }}
                </p>
                @if (filled($record->client?->phone))
                    <a href="tel:{{ preg_replace('/\s+/', '', $record->client->phone) }}"
                        class="inline-flex items-center gap-2 text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
                        <x-filament::icon icon="heroicon-o-phone" class="h-4 w-4 shrink-0 opacity-80" />
                        <span class="tabular-nums">{{ $record->client->phone }}</span>
                    </a>
                @endif
                @if (filled($record->client?->email))
                    <a href="mailto:{{ $record->client->email }}"
                        class="flex items-start gap-2 break-all text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
                        <x-filament::icon icon="heroicon-o-envelope" class="mt-0.5 h-4 w-4 shrink-0 opacity-80" />
                        <span>{{ $record->client->email }}</span>
                    </a>
                @endif
            </div>
        </section>
    </div>

    <section class="{{ $fiPanel }} p-4 sm:p-5">
        <h2 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">Предмет работы</h2>
        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Оборудование, инструменты или тип заказа</p>
        <div
            class="mt-4 max-h-[min(20rem,55vh)] overflow-y-auto overscroll-contain {{ $fiInset }} p-4">
            {!! OrderResource::orderModalWorkSubjectHtml($record) !!}
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="{{ $fiPanel }} flex min-h-0 flex-col overflow-hidden">
            <div class="border-b {{ $fiDivider }} px-4 py-3 sm:px-5">
                <h2 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">Работы</h2>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Состав и стоимость работ</p>
            </div>
            <div class="min-h-0 flex-1 space-y-3 overflow-y-auto p-4 sm:p-5 lg:max-h-[min(22rem,50vh)]">
                @forelse ($record->orderWorks as $work)
                    <article class="{{ $fiInset }} p-3">
                        <p class="whitespace-pre-wrap break-words font-medium leading-relaxed text-gray-950 dark:text-white">
                            {{ $work->description ?: '—' }}
                        </p>
                        @if (filled($work->equipment_component_name) || filled($work->equipment_component_serial_number))
                            <p class="mt-2 text-xs leading-relaxed text-gray-600 dark:text-gray-400">
                                @if (filled($work->equipment_component_name))
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Элемент:</span>
                                    {{ $work->equipment_component_name }}
                                @endif
                                @if (filled($work->equipment_component_serial_number))
                                    @if (filled($work->equipment_component_name))
                                        <span class="mx-1 text-gray-400">·</span>
                                    @endif
                                    <span class="font-medium text-gray-700 dark:text-gray-300">SN:</span>
                                    {{ $work->equipment_component_serial_number }}
                                @endif
                            </p>
                        @endif
                        <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 border-t {{ $fiDivider }} pt-3 text-xs">
                            <span class="text-gray-600 dark:text-gray-400">Работа:</span>
                            <span class="font-semibold tabular-nums text-gray-950 dark:text-white">
                                {{ number_format((float) ($work->work_price ?? 0), 2, ',', ' ') }} ₽
                            </span>
                            @if (($work->quantity ?? 1) != 1 || filled($work->unit_price))
                                <span class="text-gray-400">|</span>
                                <span class="text-gray-600 dark:text-gray-400">Кол-во</span>
                                <span class="tabular-nums font-medium">{{ $work->quantity ?? 1 }}</span>
                                @if (filled($work->unit_price))
                                    <span class="text-gray-400">|</span>
                                    <span class="text-gray-600 dark:text-gray-400">За ед.</span>
                                    <span class="tabular-nums font-medium">{{ number_format((float) $work->unit_price, 2, ',', ' ') }} ₽</span>
                                @endif
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">Нет работ</p>
                @endforelse
            </div>
        </section>

        <section class="{{ $fiPanel }} flex min-h-0 flex-col overflow-hidden">
            <div class="border-b {{ $fiDivider }} px-4 py-3 sm:px-5">
                <h2 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">Материалы</h2>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Списание со склада по заказу</p>
            </div>
            <div class="min-h-0 flex-1 space-y-3 overflow-y-auto p-4 sm:p-5 lg:max-h-[min(22rem,50vh)]">
                @forelse ($record->orderMaterials as $material)
                    @php
                        $lineTotal = (float) $material->quantity * (float) ($material->price ?? 0);
                    @endphp
                    <article class="{{ $fiInset }} p-3">
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <div class="min-w-0 font-medium leading-snug text-gray-950 dark:text-white">
                                {{ $material->name ?? '—' }}
                                @if (filled($material->article))
                                    <span class="ml-1 font-normal text-gray-500 dark:text-gray-400">· {{ $material->article }}</span>
                                @endif
                            </div>
                            <div class="shrink-0 text-sm font-semibold tabular-nums text-gray-950 dark:text-white">
                                {{ number_format($lineTotal, 2, ',', ' ') }} ₽
                            </div>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-600 dark:text-gray-400">
                            @if (filled($material->category_name))
                                <span class="rounded-md bg-gray-200/90 px-1.5 py-0.5 dark:bg-white/10">{{ $material->category_name }}</span>
                            @endif
                            <span class="tabular-nums">
                                {{ number_format((float) $material->quantity, 3, ',', ' ') }} {{ $material->unit ?? 'шт' }}
                            </span>
                            <span>× {{ number_format((float) ($material->price ?? 0), 2, ',', ' ') }} ₽</span>
                        </div>
                        @if (filled($material->notes))
                            <p class="mt-2 border-t {{ $fiDivider }} pt-2 text-xs leading-relaxed text-gray-600 dark:text-gray-400 whitespace-pre-wrap break-words">
                                {{ $material->notes }}
                            </p>
                        @endif
                    </article>
                @empty
                    <p class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">Нет материалов</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="{{ $fiPanel }} p-4 sm:p-5">
        <h2 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">Сопровождение</h2>
        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Кто ведёт заказ и откуда пришёл клиент</p>
        <dl class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <div class="{{ $fiInset }} px-3 py-2.5">
                <dt class="text-xs text-gray-500 dark:text-gray-400">Мастер</dt>
                <dd class="mt-0.5 font-medium text-gray-950 dark:text-white">{{ $record->master?->name ?? '—' }}</dd>
            </div>
            <div class="{{ $fiInset }} px-3 py-2.5">
                <dt class="text-xs text-gray-500 dark:text-gray-400">Менеджер</dt>
                <dd class="mt-0.5 font-medium text-gray-950 dark:text-white">{{ $record->manager?->name ?? '—' }}</dd>
            </div>
            <div class="{{ $fiInset }} px-3 py-2.5">
                <dt class="text-xs text-gray-500 dark:text-gray-400">Источник клиента</dt>
                <dd class="mt-0.5 font-medium text-gray-950 dark:text-white">
                    @if (filled($record->client_source))
                        {{ Order::getAvailableClientSources()[$record->client_source] ?? $record->client_source }}
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div class="{{ $fiInset }} px-3 py-2.5">
                <dt class="text-xs text-gray-500 dark:text-gray-400">Исходный заказ</dt>
                <dd class="mt-0.5 font-medium text-gray-950 dark:text-white">{{ $record->parentOrder?->order_number ?? '—' }}</dd>
            </div>
            <div class="{{ $fiInset }} px-3 py-2.5">
                <dt class="text-xs text-gray-500 dark:text-gray-400">Пометка удаления</dt>
                <dd class="mt-0.5 font-medium text-gray-950 dark:text-white">{{ $record->is_deleted ? 'Да' : 'Нет' }}</dd>
            </div>
        </dl>
    </section>
</div>
