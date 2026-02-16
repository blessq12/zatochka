@extends('documents.layouts.document')

@section('content')
    {{-- Часть для клиента --}}
    <div class="document-part client-part">
        <div class="document-part-header">ЭКЗЕМПЛЯР ДЛЯ КЛИЕНТА</div>

        <div class="company-header"
            style="margin-bottom: 2mm; padding-bottom: 2mm; border-bottom: 0.5px solid #000; width: 100%; max-width: 100%; box-sizing: border-box;">
            <div style="overflow: hidden; width: 100%;">
                <div style="float: left; width: 60%; max-width: 60%; box-sizing: border-box;">
                    @php
                        $logoPath = public_path('images/logo.png');
                        $logoExists = file_exists($logoPath);
                        if (!$logoExists) {
                            $logoPath = public_path('images/logo.jpg');
                            $logoExists = file_exists($logoPath);
                        }
                        if (!$logoExists) {
                            $logoPath = base_path('resources/js/assets/logo.svg');
                            $logoExists = file_exists($logoPath);
                        }
                    @endphp
                    @if ($logoExists)
                        <img src="{{ $logoPath }}" alt="Logo"
                            style="max-height: 12mm; max-width: 35mm; margin-bottom: 0.5mm; display: block;">
                    @endif
                    @if ($data->companyName)
                        <div style="font-size: 9px; font-weight: bold; margin-bottom: 0.5mm; letter-spacing: 0.2px;">
                            {{ $data->companyName }}</div>
                    @endif
                    @if ($data->companyLegalName)
                        <div style="font-size: 8px; margin-bottom: 0.5mm; line-height: 1.3; color: #333;">
                            {{ $data->companyLegalName }}</div>
                    @endif
                    <div style="font-size: 8px; line-height: 1.4;">
                        @if ($data->companyInn)
                            <div style="margin-bottom: 0.2mm;">ИНН: {{ $data->companyInn }}</div>
                        @endif
                        @if ($data->companyKpp)
                            <div style="margin-bottom: 0.2mm;">КПП: {{ $data->companyKpp }}</div>
                        @endif
                        @if ($data->companyOgrn)
                            <div style="margin-bottom: 0.2mm;">ОГРН: {{ $data->companyOgrn }}</div>
                        @endif
                    </div>
                </div>
                <div
                    style="float: right; width: 38%; max-width: 38%; text-align: right; font-size: 8px; box-sizing: border-box;">
                    @if ($data->companyAddress)
                        <div style="margin-bottom: 0.2mm; line-height: 1.1;">{{ $data->companyAddress }}</div>
                    @endif
                    @if ($data->companyPhone)
                        <div>Тел.: {{ $data->companyPhone }}</div>
                    @endif
                </div>
                <div style="clear: both;"></div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Информация</div>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; vertical-align: top; padding-right: 5mm;">
                        <div class="info-row">
                            <div class="info-label">Номер заказа:</div>
                            <div class="info-value">{{ $data->orderNumber }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Дата приема:</div>
                            <div class="info-value">{{ $data->orderDate }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Дата выдачи:</div>
                            <div class="info-value">{{ now()->format('d.m.Y H:i') }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Тип услуги:</div>
                            <div class="info-value">{{ $data->serviceTypeLabel }}</div>
                        </div>
                        @if ($data->equipmentName)
                            <div class="info-row">
                                <div class="info-label">Оборудование:</div>
                                <div class="info-value">{{ $data->equipmentName }}</div>
                            </div>
                        @endif
                        @if (!empty($data->tools))
                            <div class="info-row">
                                <div class="info-label">Инструменты:</div>
                                <div class="info-value">
                                    @foreach ($data->tools as $tool)
                                        {{ $tool['type'] }} ({{ $tool['quantity'] }} шт.)
                                        @if (!$loop->last)
                                            <br>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </td>
                    <td style="width: 50%; vertical-align: top; padding-left: 5mm;">
                        <div class="info-row">
                            <div class="info-label">ФИО клиента:</div>
                            <div class="info-value">{{ $data->clientName }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Телефон:</div>
                            <div class="info-value">{{ $data->clientPhone }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        @if ($data->equipmentName || !empty($data->tools))
            <div class="section">
                <table class="table" style="width: 100%; max-width: 100%; table-layout: fixed;">
                    <thead>
                        <tr>
                            <th style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Наименование предмета
                            </th>
                            <th style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000; width: 30%;">Количество
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($data->equipmentName)
                            <tr>
                                <td style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Оборудование на
                                    ремонт: {{ $data->equipmentName }}</td>
                                <td style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000; text-align: center;">
                                    1 шт.</td>
                            </tr>
                        @endif
                        @if (!empty($data->tools))
                            @foreach ($data->tools as $tool)
                                <tr>
                                    <td style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Инструменты на
                                        заточку: {{ $tool['type'] }}</td>
                                    <td
                                        style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000; text-align: center;">
                                        {{ $tool['quantity'] }} шт.</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                @if ($data->problemDescription)
                    <div style="margin-top: 2mm; font-size: 8px;">
                        <strong>Описание проблемы:</strong> {{ $data->problemDescription }}
                    </div>
                @endif
            </div>
        @elseif ($data->problemDescription)
            <div class="section">
                <div class="section-title">Описание проблемы</div>
                <div style="font-size: 8px;">{{ $data->problemDescription }}</div>
            </div>
        @endif

        @if (!empty($data->works))
            <div class="section">
                <table class="table" style="width: 100%; max-width: 100%; table-layout: fixed;">
                    <thead>
                        <tr>
                            <th style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Наименование</th>
                            @if (collect($data->works)->contains(fn($w) => !empty($w['equipment_component_serial_number'])))
                                <th style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Серийный номер</th>
                            @endif
                            <th class="text-right" style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">
                                Стоимость</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data->works as $work)
                            <tr>
                                <td style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">{{ $work['name'] }}
                                </td>
                                @if (collect($data->works)->contains(fn($w) => !empty($w['equipment_component_serial_number'])))
                                    <td style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">
                                        {{ $work['equipment_component_serial_number'] ?? '-' }}</td>
                                @endif
                                <td class="text-right" style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">
                                    {{ number_format($work['price'], 2, ',', ' ') }} ₽</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="text-right"
                                colspan="{{ collect($data->works)->contains(fn($w) => !empty($w['equipment_component_serial_number'])) ? 2 : 1 }}"
                                style="font-weight: bold; font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">
                                Итого:</td>
                            <td class="text-right"
                                style="font-weight: bold; font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">
                                {{ number_format(collect($data->works)->sum('price'), 2, ',', ' ') }} ₽
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        @if (!empty($data->materials))
            <div class="section">
                <div class="section-title">Использованные материалы/запчасти</div>
                <table class="table" style="width: 100%; max-width: 100%; table-layout: fixed;">
                    <thead>
                        <tr>
                            <th style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Наименование</th>
                            <th class="text-center" style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Кол-во</th>
                            <th class="text-right" style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Стоимость</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data->materials as $material)
                            <tr>
                                <td style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">{{ $material['name'] }}</td>
                                <td class="text-center" style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">{{ $material['quantity'] }}
                                </td>
                                <td class="text-right" style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">
                                    {{ number_format($material['price'], 2, ',', ' ') }} ₽</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="text-right" colspan="2"
                                style="font-weight: bold; font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Итого:</td>
                            <td class="text-right" style="font-weight: bold; font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">
                                {{ number_format(collect($data->materials)->sum('price'), 2, ',', ' ') }} ₽
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif


        <div class="responsibility-section">
            <div class="responsibility-title">ВАЖНАЯ ИНФОРМАЦИЯ ДЛЯ КЛИЕНТА:</div>
            <div class="responsibility-text">1. Сохраните документ как подтверждение выполнения работ.</div>
            <div class="responsibility-text">2. Претензии по качеству принимаются в течение 14 дней с момента выдачи.</div>
            <div class="responsibility-text">3. Гарантия на работы - 30 дней с момента выдачи заказа.</div>
            <div class="responsibility-text">4. Гарантия не распространяется на повреждения по вине клиента.</div>
            <div class="responsibility-text">5. При претензиях предоставьте документ и оборудование/инструмент.</div>
        </div>

        <div class="signature-section">
            <div style="float: left; width: 48%; margin-right: 4%;">
                <div
                    style="text-align: center; font-size: 9px; font-weight: bold; margin-bottom: 0.8mm; letter-spacing: 0.2px;">
                    Менеджер:</div>
                <div style="text-align: center; font-size: 8px; margin-bottom: 1.5mm; color: #333;">
                    {{ $data->managerName ?? '_________________' }}</div>
                <div
                    style="margin-top: 5mm; text-align: center; font-size: 9px; border-top: 0.5px solid #000; padding-top: 1mm;">
                    _________________</div>
                <div style="text-align: center; font-size: 8px; margin-top: 0.5mm; color: #666;">(подпись)</div>
            </div>
            <div style="float: left; width: 48%;">
                <div
                    style="text-align: center; font-size: 9px; font-weight: bold; margin-bottom: 0.8mm; letter-spacing: 0.2px;">
                    Клиент:</div>
                <div style="text-align: center; font-size: 8px; margin-bottom: 1.5mm; color: #333;">
                    {{ $data->clientName }}</div>
                <div
                    style="margin-top: 5mm; text-align: center; font-size: 9px; border-top: 0.5px solid #000; padding-top: 1mm;">
                    _________________</div>
                <div style="text-align: center; font-size: 8px; margin-top: 0.5mm; color: #666;">(подпись)</div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>

    {{-- Часть для мастерской --}}
    <div class="document-part workshop-part">
        <div class="document-part-header">ЭКЗЕМПЛЯР ДЛЯ МАСТЕРСКОЙ | № {{ $data->orderNumber }} от {{ $data->orderDate }}
        </div>

        <div class="section">
            <div class="section-title">Информация</div>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; vertical-align: top; padding-right: 5mm;">
                        <div class="info-row">
                            <div class="info-label">Номер заказа:</div>
                            <div class="info-value">{{ $data->orderNumber }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Дата приема:</div>
                            <div class="info-value">{{ $data->orderDate }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Дата выдачи:</div>
                            <div class="info-value">{{ now()->format('d.m.Y H:i') }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Тип услуги:</div>
                            <div class="info-value">{{ $data->serviceTypeLabel }}</div>
                        </div>
                        @if ($data->equipmentName)
                            <div class="info-row">
                                <div class="info-label">Оборудование:</div>
                                <div class="info-value">{{ $data->equipmentName }}</div>
                            </div>
                        @endif
                        @if (!empty($data->tools))
                            <div class="info-row">
                                <div class="info-label">Инструменты:</div>
                                <div class="info-value">
                                    @foreach ($data->tools as $tool)
                                        {{ $tool['type'] }} ({{ $tool['quantity'] }} шт.)
                                        @if (!$loop->last)
                                            <br>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </td>
                    <td style="width: 50%; vertical-align: top; padding-left: 5mm;">
                        <div class="info-row">
                            <div class="info-label">ФИО клиента:</div>
                            <div class="info-value">{{ $data->clientName }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Телефон:</div>
                            <div class="info-value">{{ $data->clientPhone }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        @if ($data->equipmentName || !empty($data->tools))
            <div class="section">
                <table class="table" style="width: 100%; max-width: 100%; table-layout: fixed;">
                    <thead>
                        <tr>
                            <th style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Наименование предмета</th>
                            <th style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000; width: 30%;">Количество
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($data->equipmentName)
                            <tr>
                                <td style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Оборудование на ремонт: {{ $data->equipmentName }}</td>
                                <td
                                    style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000; text-align: center;">
                                    1 шт.</td>
                            </tr>
                        @endif
                        @if (!empty($data->tools))
                            @foreach ($data->tools as $tool)
                                <tr>
                                    <td style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Инструменты на заточку: {{ $tool['type'] }}</td>
                                    <td
                                        style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000; text-align: center;">
                                        {{ $tool['quantity'] }} шт.</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                @if ($data->problemDescription)
                    <div style="margin-top: 2mm; font-size: 8px;">
                        <strong>Описание проблемы:</strong> {{ $data->problemDescription }}
                    </div>
                @endif
            </div>
        @elseif ($data->problemDescription)
            <div class="section">
                <div class="section-title">Описание проблемы</div>
                <div style="font-size: 8px;">{{ $data->problemDescription }}</div>
            </div>
        @endif

        @if (!empty($data->works))
            <div class="section">
                <table class="table" style="width: 100%; max-width: 100%; table-layout: fixed;">
                    <thead>
                        <tr>
                            <th style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Наименование</th>
                            @if (collect($data->works)->contains(fn($w) => !empty($w['equipment_component_serial_number'])))
                                <th style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Серийный номер</th>
                            @endif
                            <th class="text-right" style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">
                                Стоимость</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data->works as $work)
                            <tr>
                                <td style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">
                                    {{ $work['name'] }}</td>
                                @if (collect($data->works)->contains(fn($w) => !empty($w['equipment_component_serial_number'])))
                                    <td style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">
                                        {{ $work['equipment_component_serial_number'] ?? '-' }}</td>
                                @endif
                                <td class="text-right"
                                    style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">
                                    {{ number_format($work['price'], 2, ',', ' ') }} ₽</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="text-right"
                                colspan="{{ collect($data->works)->contains(fn($w) => !empty($w['equipment_component_serial_number'])) ? 2 : 1 }}"
                                style="font-weight: bold; font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">
                                Итого:</td>
                            <td class="text-right"
                                style="font-weight: bold; font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">
                                {{ number_format(collect($data->works)->sum('price'), 2, ',', ' ') }} ₽
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        @if (!empty($data->materials))
            <div class="section">
                <div class="section-title">Использованные материалы/запчасти</div>
                <table class="table" style="width: 100%; max-width: 100%; table-layout: fixed;">
                    <thead>
                        <tr>
                            <th style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Наименование</th>
                            <th class="text-center" style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Кол-во</th>
                            <th class="text-right" style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Стоимость</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data->materials as $material)
                            <tr>
                                <td style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">{{ $material['name'] }}</td>
                                <td class="text-center" style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">
                                    {{ $material['quantity'] }}</td>
                                <td class="text-right" style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">
                                    {{ number_format($material['price'], 2, ',', ' ') }} ₽</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="text-right" colspan="2"
                                style="font-weight: bold; font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Итого:</td>
                            <td class="text-right" style="font-weight: bold; font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">
                                {{ number_format(collect($data->materials)->sum('price'), 2, ',', ' ') }} ₽
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        @if ($data->masterName)
            <div class="section">
                <div class="section-title">Исполнитель</div>
                <div class="info-row">
                    <div class="info-label">Мастер:</div>
                    <div class="info-value">{{ $data->masterName }}</div>
                </div>
            </div>
        @endif

        <div class="signature-section">
            <div style="float: left; width: 48%; margin-right: 4%;">
                <div
                    style="text-align: center; font-size: 9px; font-weight: bold; margin-bottom: 0.8mm; letter-spacing: 0.2px;">
                    Менеджер:</div>
                <div style="text-align: center; font-size: 8px; margin-bottom: 1.5mm; color: #333;">
                    {{ $data->managerName ?? '_________________' }}</div>
                <div
                    style="margin-top: 5mm; text-align: center; font-size: 9px; border-top: 0.5px solid #000; padding-top: 1mm;">
                    _________________</div>
                <div style="text-align: center; font-size: 8px; margin-top: 0.5mm; color: #666;">(подпись)</div>
            </div>
            <div style="float: left; width: 48%;">
                <div
                    style="text-align: center; font-size: 9px; font-weight: bold; margin-bottom: 0.8mm; letter-spacing: 0.2px;">
                    Клиент:</div>
                <div style="text-align: center; font-size: 8px; margin-bottom: 1.5mm; color: #333;">
                    {{ $data->clientName }}</div>
                <div
                    style="margin-top: 5mm; text-align: center; font-size: 9px; border-top: 0.5px solid #000; padding-top: 1mm;">
                    _________________</div>
                <div style="text-align: center; font-size: 8px; margin-top: 0.5mm; color: #666;">(подпись)</div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
@endsection
