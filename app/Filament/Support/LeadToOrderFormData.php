<?php

namespace App\Filament\Support;

use App\Application\ClientPortal\Support\SiteLeadIntakeLabels;
use App\Domain\OrderFulfillment\Enum\OrderUrgency;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\SiteLeadModel;

final class LeadToOrderFormData
{
    /**
     * @return array<string, mixed>
     */
    public static function fromLead(SiteLeadModel $lead, ?int $managerId = null): array
    {
        $serviceType = self::resolveServiceType($lead->service_types ?? []);
        /** @var array<string, mixed> $intake */
        $intake = $lead->intake_data ?? [];

        $data = [
            'lead_id' => $lead->id,
            'client_mode' => 'guest',
            'client_full_name' => $lead->full_name,
            'client_phone' => $lead->phone,
            'service_type' => $serviceType,
            'urgency' => self::resolveUrgency($serviceType, $intake),
            'is_warranty' => false,
            'needs_delivery' => (bool) $lead->needs_delivery,
            'delivery_address' => $lead->delivery_address,
            'problem_description' => self::resolveProblemDescription($serviceType, $intake, $lead->comment),
        ];

        if ($managerId !== null) {
            $data['manager_id'] = $managerId;
        }

        if ($serviceType === 'sharpening') {
            $tools = self::resolveTools($intake);

            if ($tools !== []) {
                $data['tools'] = $tools;
            }
        }

        if ($serviceType === 'repair') {
            $data = [...$data, ...self::resolveRepairEquipment($intake)];
        }

        return $data;
    }

    /**
     * @param  list<string>  $serviceTypes
     */
    private static function resolveServiceType(array $serviceTypes): string
    {
        foreach ($serviceTypes as $type) {
            if (isset(OrderFormCommandBuilder::SERVICE_TYPE_OPTIONS[(string) $type])) {
                return (string) $type;
            }
        }

        return array_key_first(OrderFormCommandBuilder::SERVICE_TYPE_OPTIONS) ?? 'sharpening';
    }

    /**
     * @param  array<string, mixed>  $intake
     */
    private static function resolveUrgency(string $serviceType, array $intake): string
    {
        if ($serviceType === 'repair' && ($intake['urgency_type'] ?? '') === 'urgent') {
            return OrderUrgency::Urgent->value;
        }

        return OrderUrgency::Standard->value;
    }

    /**
     * @param  array<string, mixed>  $intake
     */
    private static function resolveProblemDescription(string $serviceType, array $intake, ?string $legacyComment): ?string
    {
        if ($intake !== []) {
            if ($serviceType === 'repair') {
                $problem = trim((string) ($intake['problem_description'] ?? ''));

                return $problem !== '' ? $problem : $legacyComment;
            }

            $extra = trim((string) ($intake['extra_comment'] ?? ''));

            if ($extra !== '') {
                return $extra;
            }

            return filled($legacyComment) ? $legacyComment : null;
        }

        return $legacyComment;
    }

    /**
     * @param  array<string, mixed>  $intake
     * @return list<array{name: string, tool_type: string, quantity: int}>
     */
    private static function resolveTools(array $intake): array
    {
        $toolType = (string) ($intake['tool_type'] ?? '');

        if ($toolType === '' || ! isset(OrderFormCommandBuilder::TOOL_TYPE_OPTIONS[$toolType])) {
            return [];
        }

        $quantity = max(1, (int) ($intake['tools_count'] ?? 1));
        $label = SiteLeadIntakeLabels::toolTypeLabel($toolType);

        return [
            [
                'name' => sprintf('%s (%d шт.)', $label, $quantity),
                'tool_type' => $toolType,
                'quantity' => $quantity,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $intake
     * @return array<string, mixed>
     */
    private static function resolveRepairEquipment(array $intake): array
    {
        $deviceName = trim((string) ($intake['device_name'] ?? ''));

        if ($deviceName === '') {
            return [];
        }

        $equipmentType = (string) ($intake['equipment_type'] ?? '');
        $typeLabel = $equipmentType !== ''
            ? SiteLeadIntakeLabels::equipmentTypeLabel($equipmentType)
            : null;

        return [
            'equipment_mode' => 'new',
            'equipment_name' => $deviceName,
            'equipment_model' => $typeLabel,
        ];
    }
}
