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
                        <div style="font-size: 5px; font-weight: bold; margin-bottom: 0.5mm; letter-spacing: 0.2px;">
                            {{ $data->companyName }}</div>
                    @endif
                    @if ($data->companyLegalName)
                        <div style="font-size: 4px; margin-bottom: 0.5mm; line-height: 1.3; color: #333;">
                            {{ $data->companyLegalName }}</div>
                    @endif
                    <div style="font-size: 4px; line-height: 1.4;">
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
                    style="float: right; width: 38%; max-width: 38%; text-align: right; font-size: 4px; box-sizing: border-box;">
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
            <div class="section-title">Основная информация</div>
            <table class="table" style="font-size: 5px; width: 100%; max-width: 100%; table-layout: fixed;">
                <tr>
                    <td style="width: 35%; font-weight: bold; padding: 1px;">ФИО клиента:</td>
                    <td style="padding: 1px;">{{ $data->clientName }}</td>
                    <td style="width: 35%; font-weight: bold; padding: 1px;">Номер заказа:</td>
                    <td style="padding: 1px;">{{ $data->orderNumber }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 1px;">Телефон:</td>
                    <td style="padding: 1px;">{{ $data->clientPhone }}</td>
                    <td style="font-weight: bold; padding: 1px;">Дата приема:</td>
                    <td style="padding: 1px;">{{ $data->orderDate }}</td>
                </tr>
                @if ($data->clientEmail)
                    <tr>
                        <td style="font-weight: bold; padding: 1px;">Email:</td>
                        <td style="padding: 1px;">{{ $data->clientEmail }}</td>
                        <td style="font-weight: bold; padding: 1px;">Тип услуги:</td>
                        <td style="padding: 1px;">
                            {{ $data->serviceTypeLabel }}
                            @if ($data->urgency === 'Срочный')
                                <span class="urgent-badge">СРОЧНО</span>
                            @endif
                        </td>
                    </tr>
                @else
                    <tr>
                        <td colspan="2" style="padding: 1px;"></td>
                        <td style="font-weight: bold; padding: 1px;">Тип услуги:</td>
                        <td style="padding: 1px;">
                            {{ $data->serviceTypeLabel }}
                            @if ($data->urgency === 'Срочный')
                                <span class="urgent-badge">СРОЧНО</span>
                            @endif
                        </td>
                    </tr>
                @endif
                @if ($data->equipmentName)
                    <tr>
                        <td colspan="2" style="padding: 1px;"></td>
                        <td style="font-weight: bold; padding: 1px;">Оборудование:</td>
                        <td style="padding: 1px;">{{ $data->equipmentName }}</td>
                    </tr>
                @endif
                @if (!empty($data->tools))
                    <tr>
                        <td colspan="2" style="padding: 1px;"></td>
                        <td style="font-weight: bold; padding: 1px;">Инструменты:</td>
                        <td style="padding: 1px;">
                            @foreach ($data->tools as $tool)
                                {{ $tool['type'] }} ({{ $tool['quantity'] }} шт.)
                                @if (isset($tool['description']) && $tool['description'])
                                    - {{ $tool['description'] }}
                                @endif
                                @if (!$loop->last)
                                    <br>
                                @endif
                            @endforeach
                        </td>
                    </tr>
                @endif
                @if ($data->problemDescription)
                    <tr>
                        <td style="font-weight: bold; padding: 1px;">Описание проблемы:</td>
                        <td colspan="3" style="padding: 1px;">{{ $data->problemDescription }}</td>
                    </tr>
                @endif
                <tr>
                    <td style="font-weight: bold; padding: 1px;">Филиал:</td>
                    <td style="padding: 1px;">{{ $data->branchName }}</td>
                    @if ($data->branchAddress)
                        <td style="font-weight: bold; padding: 1px;">Адрес филиала:</td>
                        <td style="padding: 1px;">{{ $data->branchAddress }}</td>
                    @else
                        <td colspan="2" style="padding: 1px;"></td>
                    @endif
                </tr>
                @if ($data->branchPhone)
                    <tr>
                        <td colspan="2" style="padding: 1px;"></td>
                        <td style="font-weight: bold; padding: 1px;">Телефон филиала:</td>
                        <td style="padding: 1px;">{{ $data->branchPhone }}</td>
                    </tr>
                @endif
            </table>
        </div>

        @if ($data->price)
            <div class="section">
                <div class="section-title">Предварительная стоимость</div>
                <div class="info-row">
                    <div class="info-label">Ориентировочная стоимость:</div>
                    <div class="info-value">{{ number_format($data->price, 2, ',', ' ') }} ₽</div>
                </div>
            </div>
        @endif

        @if ($data->needsDelivery && $data->deliveryAddress)
            <div class="section">
                <div class="section-title">Доставка</div>
                <div class="info-row">
                    <div class="info-label">Требуется доставка:</div>
                    <div class="info-value">Да</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Адрес доставки:</div>
                    <div class="info-value">{{ $data->deliveryAddress }}</div>
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
                    style="text-align: center; font-size: 5px; font-weight: bold; margin-bottom: 0.8mm; letter-spacing: 0.2px;">
                    Менеджер:</div>
                <div style="text-align: center; font-size: 4px; margin-bottom: 1.5mm; color: #333;">
                    {{ $data->managerName ?? '_________________' }}</div>
                <div
                    style="margin-top: 10mm; text-align: center; font-size: 5px; border-top: 0.5px solid #000; padding-top: 1mm;">
                    _________________</div>
                <div style="text-align: center; font-size: 4px; margin-top: 0.5mm; color: #666;">(подпись)</div>
            </div>
            <div style="float: left; width: 48%;">
                <div
                    style="text-align: center; font-size: 5px; font-weight: bold; margin-bottom: 0.8mm; letter-spacing: 0.2px;">
                    Клиент:</div>
                <div style="text-align: center; font-size: 4px; margin-bottom: 1.5mm; color: #333;">{{ $data->clientName }}
                </div>
                <div
                    style="margin-top: 10mm; text-align: center; font-size: 5px; border-top: 0.5px solid #000; padding-top: 1mm;">
                    _________________</div>
                <div style="text-align: center; font-size: 4px; margin-top: 0.5mm; color: #666;">(подпись)</div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>

    {{-- Часть для мастерской --}}
    <div class="document-part workshop-part">
        <div class="document-part-header">ЭКЗЕМПЛЯР ДЛЯ МАСТЕРСКОЙ</div>

        <div class="section">
            <div class="section-title">Основная информация</div>
            <table class="table" style="font-size: 5px; width: 100%; max-width: 100%; table-layout: fixed;">
                <tr>
                    <td style="width: 35%; font-weight: bold; padding: 1px;">ФИО клиента:</td>
                    <td style="padding: 1px;">{{ $data->clientName }}</td>
                    <td style="width: 35%; font-weight: bold; padding: 1px;">Номер заказа:</td>
                    <td style="padding: 1px;">{{ $data->orderNumber }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 1px;">Телефон:</td>
                    <td style="padding: 1px;">{{ $data->clientPhone }}</td>
                    <td style="font-weight: bold; padding: 1px;">Дата приема:</td>
                    <td style="padding: 1px;">{{ $data->orderDate }}</td>
                </tr>
                @if ($data->clientEmail)
                    <tr>
                        <td style="font-weight: bold; padding: 1px;">Email:</td>
                        <td style="padding: 1px;">{{ $data->clientEmail }}</td>
                        <td style="font-weight: bold; padding: 1px;">Тип услуги:</td>
                        <td style="padding: 1px;">
                            {{ $data->serviceTypeLabel }}
                            @if ($data->urgency === 'Срочный')
                                <span class="urgent-badge">СРОЧНО</span>
                            @endif
                        </td>
                    </tr>
                @else
                    <tr>
                        <td colspan="2" style="padding: 1px;"></td>
                        <td style="font-weight: bold; padding: 1px;">Тип услуги:</td>
                        <td style="padding: 1px;">
                            {{ $data->serviceTypeLabel }}
                            @if ($data->urgency === 'Срочный')
                                <span class="urgent-badge">СРОЧНО</span>
                            @endif
                        </td>
                    </tr>
                @endif
                @if ($data->equipmentName)
                    <tr>
                        <td colspan="2" style="padding: 1px;"></td>
                        <td style="font-weight: bold; padding: 1px;">Оборудование:</td>
                        <td style="padding: 1px;">{{ $data->equipmentName }}</td>
                    </tr>
                @endif
                @if (!empty($data->tools))
                    <tr>
                        <td colspan="2" style="padding: 1px;"></td>
                        <td style="font-weight: bold; padding: 1px;">Инструменты:</td>
                        <td style="padding: 1px;">
                            @foreach ($data->tools as $tool)
                                {{ $tool['type'] }} ({{ $tool['quantity'] }} шт.)
                                @if (isset($tool['description']) && $tool['description'])
                                    - {{ $tool['description'] }}
                                @endif
                                @if (!$loop->last)
                                    <br>
                                @endif
                            @endforeach
                        </td>
                    </tr>
                @endif
                @if ($data->problemDescription)
                    <tr>
                        <td style="font-weight: bold; padding: 1px;">Описание проблемы:</td>
                        <td colspan="3" style="padding: 1px;">{{ $data->problemDescription }}</td>
                    </tr>
                @endif
                <tr>
                    <td style="font-weight: bold; padding: 1px;">Филиал:</td>
                    <td style="padding: 1px;">{{ $data->branchName }}</td>
                    @if ($data->branchAddress)
                        <td style="font-weight: bold; padding: 1px;">Адрес филиала:</td>
                        <td style="padding: 1px;">{{ $data->branchAddress }}</td>
                    @else
                        <td colspan="2" style="padding: 1px;"></td>
                    @endif
                </tr>
                @if ($data->branchPhone)
                    <tr>
                        <td colspan="2" style="padding: 1px;"></td>
                        <td style="font-weight: bold; padding: 1px;">Телефон филиала:</td>
                        <td style="padding: 1px;">{{ $data->branchPhone }}</td>
                    </tr>
                @endif
            </table>
        </div>

        @if ($data->price)
            <div class="section">
                <div class="section-title">Предварительная стоимость</div>
                <div class="info-row">
                    <div class="info-label">Ориентировочная стоимость:</div>
                    <div class="info-value">{{ number_format($data->price, 2, ',', ' ') }} ₽</div>
                </div>
            </div>
        @endif

        @if ($data->needsDelivery && $data->deliveryAddress)
            <div class="section">
                <div class="section-title">Доставка</div>
                <div class="info-row">
                    <div class="info-label">Требуется доставка:</div>
                    <div class="info-value">Да</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Адрес доставки:</div>
                    <div class="info-value">{{ $data->deliveryAddress }}</div>
                </div>
            </div>
        @endif

        @if ($data->managerName)
            <div class="section">
                <div class="section-title">Ответственные</div>
                <div class="info-row">
                    <div class="info-label">Менеджер:</div>
                    <div class="info-value">{{ $data->managerName }}</div>
                </div>
            </div>
        @endif

        <div class="signature-section">
            <div style="float: left; width: 48%; margin-right: 4%;">
                <div
                    style="text-align: center; font-size: 5px; font-weight: bold; margin-bottom: 0.8mm; letter-spacing: 0.2px;">
                    Менеджер:</div>
                <div style="text-align: center; font-size: 4px; margin-bottom: 1.5mm; color: #333;">
                    {{ $data->managerName ?? '_________________' }}</div>
                <div
                    style="margin-top: 10mm; text-align: center; font-size: 5px; border-top: 0.5px solid #000; padding-top: 1mm;">
                    _________________</div>
                <div style="text-align: center; font-size: 4px; margin-top: 0.5mm; color: #666;">(подпись)</div>
            </div>
            <div style="float: left; width: 48%;">
                <div
                    style="text-align: center; font-size: 5px; font-weight: bold; margin-bottom: 0.8mm; letter-spacing: 0.2px;">
                    Клиент:</div>
                <div style="text-align: center; font-size: 4px; margin-bottom: 1.5mm; color: #333;">
                    {{ $data->clientName }}</div>
                <div
                    style="margin-top: 10mm; text-align: center; font-size: 5px; border-top: 0.5px solid #000; padding-top: 1mm;">
                    _________________</div>
                <div style="text-align: center; font-size: 4px; margin-top: 0.5mm; color: #666;">(подпись)</div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
@endsection
