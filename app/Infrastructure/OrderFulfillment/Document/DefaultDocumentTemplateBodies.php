<?php

namespace App\Infrastructure\OrderFulfillment\Document;

use App\Domain\OrderFulfillment\Enum\DocumentType;

final class DefaultDocumentTemplateBodies
{
    public static function forType(DocumentType $type): string
    {
        return match ($type) {
            DocumentType::Receipt => self::receipt(),
            DocumentType::HandoverAct => self::handoverAct(),
        };
    }

    private static function receipt(): string
    {
        return self::clientPart(
            responsibility: <<<'HTML'
<div class="responsibility-section">
    <div class="responsibility-title">ВАЖНАЯ ИНФОРМАЦИЯ ДЛЯ КЛИЕНТА:</div>
    <div class="responsibility-text">1. Сохраните документ до получения заказа.</div>
    <div class="responsibility-text">2. При выдаче предъявите документ или удостоверение личности.</div>
    <div class="responsibility-text">3. При утере документа выдача возможна при предъявлении удостоверения личности.</div>
    <div class="responsibility-text">4. Стоимость может быть изменена после диагностики.</div>
    <div class="responsibility-text">5. Срок хранения невостребованных заказов - 30 дней с момента готовности.</div>
</div>
HTML,
            infoBlock: self::receiptInfoBlock(),
            extra: '{{price.section}}',
        ).self::workshopPart(
            infoBlock: self::receiptInfoBlock(),
            extra: '{{price.section}}',
        );
    }

    private static function handoverAct(): string
    {
        return self::clientPart(
            responsibility: <<<'HTML'
<div class="responsibility-section">
    <div class="responsibility-title">ВАЖНАЯ ИНФОРМАЦИЯ ДЛЯ КЛИЕНТА:</div>
    <div class="responsibility-text">1. Сохраните документ как подтверждение выполнения работ.</div>
    <div class="responsibility-text">2. Претензии по качеству принимаются в течение 14 дней с момента выдачи.</div>
    <div class="responsibility-text">3. Гарантия на работы - 30 дней с момента выдачи заказа.</div>
    <div class="responsibility-text">4. Гарантия не распространяется на повреждения по вине клиента.</div>
    <div class="responsibility-text">5. При претензиях предоставьте документ и оборудование/инструмент.</div>
</div>
HTML,
            infoBlock: self::handoverInfoBlock(),
            extra: '{{works.table}}{{materials.table}}',
        ).self::workshopPart(
            infoBlock: self::handoverInfoBlock(),
            extra: '{{works.table}}{{materials.table}}{{master.section}}',
        );
    }

    private static function receiptInfoBlock(): string
    {
        return <<<'HTML'
<div class="section">
    <div style="font-size: 10px; font-weight: bold; margin-bottom: 1mm; border-bottom: 0.5px solid #000; padding-bottom: 0.5mm;">Информация</div>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 5mm;">
                <div class="info-row"><div class="info-label">Тип услуги:</div><div class="info-value">{{service.type_with_urgency}}</div></div>
                <div class="info-row"><div class="info-label">Филиал:</div><div class="info-value">{{branch.name}}</div></div>
                {{#if branch.address}}<div class="info-row"><div class="info-label">Адрес филиала:</div><div class="info-value">{{branch.address}}</div></div>{{/if}}
                {{#if branch.phone}}<div class="info-row"><div class="info-label">Телефон филиала:</div><div class="info-value">{{branch.phone}}</div></div>{{/if}}
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 5mm;">
                <div class="info-row"><div class="info-label">ФИО клиента:</div><div class="info-value">{{client.name}}</div></div>
                <div class="info-row"><div class="info-label">Телефон:</div><div class="info-value">{{client.phone}}</div></div>
            </td>
        </tr>
    </table>
</div>
HTML;
    }

    private static function handoverInfoBlock(): string
    {
        return <<<'HTML'
<div class="section">
    <div class="section-title">Информация</div>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 5mm;">
                <div class="info-row"><div class="info-label">Номер заказа:</div><div class="info-value">{{order.number}}</div></div>
                <div class="info-row"><div class="info-label">Дата приема:</div><div class="info-value">{{order.date}}</div></div>
                <div class="info-row"><div class="info-label">Дата выдачи:</div><div class="info-value">{{document.date}}</div></div>
                <div class="info-row"><div class="info-label">Тип услуги:</div><div class="info-value">{{service.type}}</div></div>
                {{#if equipment.name}}<div class="info-row"><div class="info-label">Оборудование:</div><div class="info-value">{{equipment.name}}</div></div>{{/if}}
                {{#if tools.list}}<div class="info-row"><div class="info-label">Инструменты:</div><div class="info-value">{{tools.list}}</div></div>{{/if}}
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 5mm;">
                <div class="info-row"><div class="info-label">ФИО клиента:</div><div class="info-value">{{client.name}}</div></div>
                <div class="info-row"><div class="info-label">Телефон:</div><div class="info-value">{{client.phone}}</div></div>
            </td>
        </tr>
    </table>
</div>
HTML;
    }

    private static function clientPart(string $responsibility, string $infoBlock, string $extra = ''): string
    {
        $signatures = self::signatures();

        return <<<HTML
<div class="document-part client-part">
    <div class="document-part-header">ЭКЗЕМПЛЯР ДЛЯ КЛИЕНТА</div>
    {{logo}}
    <div class="company-header" style="margin-bottom: 2mm; padding-bottom: 2mm; border-bottom: 0.5px solid #000; font-size: 8px; line-height: 1.3;">{{company.header}}</div>
    {$infoBlock}
    {{items.section}}
    {$extra}
    {$responsibility}
    {$signatures}
</div>
HTML;
    }

    private static function workshopPart(string $infoBlock, string $extra = ''): string
    {
        $signatures = self::signatures();

        return <<<HTML
<div class="document-part workshop-part">
    <div class="document-part-header">ЭКЗЕМПЛЯР ДЛЯ МАСТЕРСКОЙ | № {{order.number}} от {{order.date}}</div>
    {$infoBlock}
    {{items.section}}
    {$extra}
    {$signatures}
</div>
HTML;
    }

    private static function signatures(): string
    {
        return <<<'HTML'
<div class="signature-section">
    <div style="float: left; width: 48%; margin-right: 4%;">
        <div style="text-align: center; font-size: 9px; font-weight: bold; margin-bottom: 0.8mm; letter-spacing: 0.2px;">Менеджер:</div>
        <div style="text-align: center; font-size: 8px; margin-bottom: 1.5mm; color: #333;">{{manager.name}}</div>
        <div style="margin-top: 5mm; text-align: center; font-size: 9px; border-top: 0.5px solid #000; padding-top: 1mm;">_________________</div>
        <div style="text-align: center; font-size: 8px; margin-top: 0.5mm; color: #666;">(подпись)</div>
    </div>
    <div style="float: left; width: 48%;">
        <div style="text-align: center; font-size: 9px; font-weight: bold; margin-bottom: 0.8mm; letter-spacing: 0.2px;">Клиент:</div>
        <div style="text-align: center; font-size: 8px; margin-bottom: 1.5mm; color: #333;">{{client.name}}</div>
        <div style="margin-top: 5mm; text-align: center; font-size: 9px; border-top: 0.5px solid #000; padding-top: 1mm;">_________________</div>
        <div style="text-align: center; font-size: 8px; margin-top: 0.5mm; color: #666;">(подпись)</div>
    </div>
    <div style="clear: both;"></div>
</div>
HTML;
    }
}
