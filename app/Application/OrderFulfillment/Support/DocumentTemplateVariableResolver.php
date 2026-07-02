<?php

namespace App\Application\OrderFulfillment\Support;

use App\Application\OrderFulfillment\ReadModel\OrderDocumentData;

final class DocumentTemplateVariableResolver
{
    /** @var list<string> */
    private const RAW_HTML_KEYS = [
        'logo',
        'company.header',
        'service.type_with_urgency',
        'items.section',
        'problem.section',
        'price.section',
        'works.table',
        'materials.table',
        'master.section',
        'tools.list',
    ];

    /**
     * @return array<string, mixed>
     */
    public function resolve(OrderDocumentData $data, string $documentTitle): array
    {
        $works = array_map(static function (array $work): array {
            return [
                'description' => $work['description'],
                'price' => $work['price'],
                'price_formatted' => self::formatMoney($work['price']),
            ];
        }, $data->works);

        $materials = array_map(static function (array $material): array {
            return [
                'name' => $material['name'],
                'quantity' => $material['quantity'],
                'price' => $material['price'],
                'price_formatted' => self::formatMoney($material['price']),
            ];
        }, $data->materials);

        return [
            'order.number' => $data->orderNumber,
            'order.date' => $data->orderDate,
            'order.price' => $data->price !== null ? self::formatMoney($data->price) : '',
            'order.urgency' => $data->urgency ?? '',
            'document.title' => $documentTitle,
            'document.date' => now()->format('d.m.Y H:i'),
            'service.type' => $data->serviceTypeLabel,
            'service.type_with_urgency' => $this->serviceTypeWithUrgency($data),
            'branch.name' => $data->branchName,
            'branch.address' => $data->branchAddress ?? '',
            'branch.phone' => $data->branchPhone ?? '',
            'client.name' => $data->clientName,
            'client.phone' => $data->clientPhone,
            'equipment.name' => $data->equipmentName ?? '',
            'problem.description' => $data->problemDescription ?? '',
            'manager.name' => $data->managerName ?? '_________________',
            'master.name' => $data->masterName ?? '',
            'company.name' => $data->companyName ?? '',
            'company.legal_name' => $data->companyLegalName ?? '',
            'company.inn' => $data->companyInn ?? '',
            'company.kpp' => $data->companyKpp ?? '',
            'company.ogrn' => $data->companyOgrn ?? '',
            'company.address' => $data->companyAddress ?? '',
            'company.phone' => $data->companyPhone ?? '',
            'logo' => $this->logoHtml(),
            'company.header' => $this->companyHeader($data),
            'items.section' => $this->itemsSection($data),
            'problem.section' => $this->problemSection($data),
            'price.section' => $this->priceSection($data),
            'works.table' => $this->worksTable($works),
            'materials.table' => $this->materialsTable($materials),
            'master.section' => $this->masterSection($data),
            'tools.list' => $this->toolsList($data->tools),
            'tools' => $data->tools,
            'works' => $works,
            'materials' => $materials,
        ];
    }

    public function isRawHtmlKey(string $key): bool
    {
        return in_array($key, self::RAW_HTML_KEYS, true);
    }

    private function serviceTypeWithUrgency(OrderDocumentData $data): string
    {
        $html = e($data->serviceTypeLabel);

        if ($data->urgency === 'Срочный') {
            $html .= ' <span class="urgent-badge">СРОЧНО</span>';
        }

        return $html;
    }

    private function logoHtml(): string
    {
        $paths = [
            public_path('images/logo.png'),
            public_path('images/logo.jpg'),
            base_path('resources/js/assets/logo.svg'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return '<div style="margin-bottom: 2mm;"><img src="'.e($path).'" alt="Logo" style="max-height: 12mm; max-width: 35mm; display: block;"></div>';
            }
        }

        return '';
    }

    private function companyHeader(OrderDocumentData $data): string
    {
        $parts = array_filter([
            $data->companyName,
            $data->companyLegalName,
            $data->companyInn ? 'ИНН '.$data->companyInn : null,
            $data->companyKpp ? 'КПП '.$data->companyKpp : null,
            $data->companyOgrn ? 'ОГРН '.$data->companyOgrn : null,
            $data->companyAddress,
            $data->companyPhone ? 'Тел. '.$data->companyPhone : null,
        ]);

        return e(implode(', ', $parts));
    }

    /**
     * @param  list<array{type: string, quantity: int}>  $tools
     */
    private function itemsSection(OrderDocumentData $data): string
    {
        if ($data->equipmentName === null && $data->tools === []) {
            return $this->problemSection($data);
        }

        $rows = '';

        if ($data->equipmentName !== null) {
            $rows .= '<tr><td style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Оборудование на ремонт: '.e($data->equipmentName).'</td><td style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000; text-align: center;">1 шт.</td></tr>';
        }

        foreach ($data->tools as $tool) {
            $rows .= '<tr><td style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Инструменты на заточку: '.e($tool['type']).'</td><td style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000; text-align: center;">'.e((string) $tool['quantity']).' шт.</td></tr>';
        }

        $problem = '';
        if ($data->problemDescription) {
            $problem = '<div style="margin-top: 2mm; font-size: 8px;"><strong>Описание проблемы:</strong> '.e($data->problemDescription).'</div>';
        }

        return '<div class="section"><table class="table" style="width: 100%; max-width: 100%; table-layout: fixed;"><thead><tr><th style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Наименование предмета</th><th style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000; width: 30%;">Количество</th></tr></thead><tbody>'.$rows.'</tbody></table>'.$problem.'</div>';
    }

    private function problemSection(OrderDocumentData $data): string
    {
        if (! $data->problemDescription) {
            return '';
        }

        return '<div class="section"><div class="section-title">Описание проблемы</div><div style="font-size: 8px;">'.e($data->problemDescription).'</div></div>';
    }

    private function priceSection(OrderDocumentData $data): string
    {
        if ($data->price === null) {
            return '';
        }

        return '<div class="section"><div class="section-title">Предварительная стоимость</div><div class="info-row"><div class="info-label">Ориентировочная стоимость:</div><div class="info-value">'.self::formatMoney($data->price).' ₽</div></div></div>';
    }

    /**
     * @param  list<array{description: string, price: float, price_formatted: string}>  $works
     */
    private function worksTable(array $works): string
    {
        if ($works === []) {
            return '';
        }

        $rows = '';
        $total = 0.0;

        foreach ($works as $work) {
            $total += $work['price'];
            $rows .= '<tr><td style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">'.e($work['description']).'</td><td class="text-right" style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">'.e($work['price_formatted']).' ₽</td></tr>';
        }

        return '<div class="section"><table class="table" style="width: 100%; max-width: 100%; table-layout: fixed;"><thead><tr><th style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Наименование</th><th class="text-right" style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Стоимость</th></tr></thead><tbody>'.$rows.'</tbody><tfoot><tr><td class="text-right" style="font-weight: bold; font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Итого:</td><td class="text-right" style="font-weight: bold; font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">'.self::formatMoney($total).' ₽</td></tr></tfoot></table></div>';
    }

    /**
     * @param  list<array{name: string, quantity: string, price: float, price_formatted: string}>  $materials
     */
    private function materialsTable(array $materials): string
    {
        if ($materials === []) {
            return '';
        }

        $rows = '';
        $total = 0.0;

        foreach ($materials as $material) {
            $total += $material['price'];
            $rows .= '<tr><td style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">'.e($material['name']).'</td><td class="text-center" style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">'.e($material['quantity']).'</td><td class="text-right" style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">'.e($material['price_formatted']).' ₽</td></tr>';
        }

        return '<div class="section"><div class="section-title">Использованные материалы/запчасти</div><table class="table" style="width: 100%; max-width: 100%; table-layout: fixed;"><thead><tr><th style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Наименование</th><th class="text-center" style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Кол-во</th><th class="text-right" style="font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Стоимость</th></tr></thead><tbody>'.$rows.'</tbody><tfoot><tr><td class="text-right" colspan="2" style="font-weight: bold; font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">Итого:</td><td class="text-right" style="font-weight: bold; font-size: 8px; padding: 2px 3px; border: 0.5px solid #000;">'.self::formatMoney($total).' ₽</td></tr></tfoot></table></div>';
    }

    private function masterSection(OrderDocumentData $data): string
    {
        if (! $data->masterName) {
            return '';
        }

        return '<div class="section"><div class="section-title">Исполнитель</div><div class="info-row"><div class="info-label">Мастер:</div><div class="info-value">'.e($data->masterName).'</div></div></div>';
    }

    /**
     * @param  list<array{type: string, quantity: int}>  $tools
     */
    private function toolsList(array $tools): string
    {
        if ($tools === []) {
            return '';
        }

        $parts = [];
        foreach ($tools as $tool) {
            $parts[] = e($tool['type']).' ('.e((string) $tool['quantity']).' шт.)';
        }

        return implode('<br>', $parts);
    }

    private static function formatMoney(float $amount): string
    {
        return number_format($amount, 2, ',', ' ');
    }
}
