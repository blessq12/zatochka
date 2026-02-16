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
            <div
                style="font-size: 10px; font-weight: bold; margin-bottom: 1mm; border-bottom: 0.5px solid #000; padding-bottom: 0.5mm;">
                Информация</div>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; vertical-align: top; padding-right: 5mm;">
                        <div class="info-row">
                            <div class="info-label">Тип услуги:</div>
                            <div class="info-value">
                                {{ $data->serviceTypeLabel }}
                                @if ($data->urgency === 'Срочный')
                                    <span class="urgent-badge">СРОЧНО</span>
                                @endif
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Филиал:</div>
                            <div class="info-value">{{ $data->branchName }}</div>
                        </div>
                        @if ($data->branchAddress)
                            <div class="info-row">
                                <div class="info-label">Адрес филиала:</div>
                                <div class="info-value">{{ $data->branchAddress }}</div>
                            </div>
                        @endif
                        @if ($data->branchPhone)
                            <div class="info-row">
                                <div class="info-label">Телефон филиала:</div>
                                <div class="info-value">{{ $data->branchPhone }}</div>
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

        @if ($data->price)
            <div class="section">
                <div class="section-title">Предварительная стоимость</div>
                <div class="info-row">
                    <div class="info-label">Ориентировочная стоимость:</div>
                    <div class="info-value">{{ number_format($data->price, 2, ',', ' ') }} ₽</div>
                </div>
            </div>
        @endif

        <div class="responsibility-section">
            <div class="responsibility-title">ВАЖНАЯ ИНФОРМАЦИЯ ДЛЯ КЛИЕНТА:</div>
            <div class="responsibility-text">1. Сохраните документ до получения заказа.</div>
            <div class="responsibility-text">2. При выдаче предъявите документ или удостоверение личности.</div>
            <div class="responsibility-text">3. При утере документа выдача возможна при предъявлении удостоверения личности.
            </div>
            <div class="responsibility-text">4. Стоимость может быть изменена после диагностики.</div>
            <div class="responsibility-text">5. Срок хранения невостребованных заказов - 30 дней с момента готовности.</div>
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
                <div style="text-align: center; font-size: 8px; margin-bottom: 1.5mm; color: #333;">{{ $data->clientName }}
                </div>
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
            <div
                style="font-size: 10px; font-weight: bold; margin-bottom: 1mm; border-bottom: 0.5px solid #000; padding-bottom: 0.5mm;">
                Основная информация
            </div>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; vertical-align: top; padding-right: 5mm;">

                        <div class="info-row">
                            <div class="info-label">Тип услуги:</div>
                            <div class="info-value">
                                {{ $data->serviceTypeLabel }}
                                @if ($data->urgency === 'Срочный')
                                    <span class="urgent-badge">СРОЧНО</span>
                                @endif
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Филиал:</div>
                            <div class="info-value">{{ $data->branchName }}</div>
                        </div>
                        @if ($data->branchAddress)
                            <div class="info-row">
                                <div class="info-label">Адрес филиала:</div>
                                <div class="info-value">{{ $data->branchAddress }}</div>
                            </div>
                        @endif
                        @if ($data->branchPhone)
                            <div class="info-row">
                                <div class="info-label">Телефон филиала:</div>
                                <div class="info-value">{{ $data->branchPhone }}</div>
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
                                <td
                                    style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000; text-align: center;">
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

        @if ($data->price)
            <div class="section">
                <div class="section-title">Предварительная стоимость</div>
                <div class="info-row">
                    <div class="info-label">Ориентировочная стоимость:</div>
                    <div class="info-value">{{ number_format($data->price, 2, ',', ' ') }} ₽</div>
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
