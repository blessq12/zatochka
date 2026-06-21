<?php

namespace App\Filament\Support;

use App\Application\Equipment\Command\RegisterEquipmentCommand;
use App\Application\Equipment\CommandHandler\RegisterEquipmentHandler;
use App\Application\OrderFulfillment\Command\CreateOrderCommand;
use App\Domain\OrderFulfillment\Entity\OrderTool;
use App\Domain\OrderFulfillment\Enum\OrderUrgency;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\ClientModel;
use RuntimeException;

final class OrderFormCommandBuilder
{
    /** @var array<string, string> */
    public const SERVICE_TYPE_OPTIONS = [
        'sharpening' => 'Заточка',
        'repair' => 'Ремонт',
        'diagnosis' => 'Диагностика',
    ];

    /** @var array<string, string> */
    public const TOOL_TYPE_OPTIONS = [
        'manicure' => 'Маникюрные',
        'hair' => 'Парикмахерские',
        'grooming' => 'Грумерские',
        'groomer' => 'Грумерские',
        'barber' => 'Барберские',
        'other' => 'Другие',
    ];

    /**
     * @param  array<string, mixed>  $data
     */
    public static function buildCommand(array $data): CreateOrderCommand
    {
        $serviceType = (string) ($data['service_type'] ?? '');

        if ($serviceType === '') {
            throw new RuntimeException('Укажите тип заказа.');
        }

        $isWarranty = (bool) ($data['is_warranty'] ?? false);
        $warrantyParentOrderId = $isWarranty
            ? (int) ($data['warranty_parent_order_id'] ?? 0)
            : null;

        if ($isWarranty && ($warrantyParentOrderId === null || $warrantyParentOrderId === 0)) {
            throw new RuntimeException('Для гарантийного заказа укажите исходный заказ.');
        }

        [$clientId, $clientSnapshot] = self::resolveClient($data);

        $equipmentId = $serviceType === 'repair'
            ? self::resolveEquipmentId($data)
            : null;

        $tools = $serviceType === 'sharpening'
            ? self::resolveTools($data)
            : [];

        if ($serviceType === 'sharpening' && $tools === []) {
            throw new RuntimeException('Добавьте хотя бы один инструмент.');
        }

        if ($serviceType === 'repair' && $equipmentId === null) {
            throw new RuntimeException('Укажите оборудование для ремонта.');
        }

        $masterIdRaw = (int) ($data['master_id'] ?? 0);
        $masterId = $masterIdRaw > 0 ? $masterIdRaw : null;
        $managerId = (int) ($data['manager_id'] ?? 0);

        if ($managerId === 0) {
            throw new RuntimeException('Назначьте менеджера.');
        }

        return new CreateOrderCommand(
            serviceTypes: [$serviceType],
            clientId: $clientId,
            clientSnapshot: $clientSnapshot,
            urgency: isset($data['urgency'])
                ? OrderUrgency::from($data['urgency'])
                : OrderUrgency::Standard,
            isWarranty: $isWarranty,
            needsDelivery: (bool) ($data['needs_delivery'] ?? false),
            deliveryAddress: filled($data['delivery_address'] ?? null)
                ? (string) $data['delivery_address']
                : null,
            problemDescription: filled($data['problem_description'] ?? null)
                ? (string) $data['problem_description']
                : null,
            equipmentId: $equipmentId,
            warrantyParentOrderId: $warrantyParentOrderId,
            masterId: $masterId,
            managerId: $managerId,
            tools: $tools,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: ?int, 1: ?ClientSnapshot}
     */
    private static function resolveClient(array $data): array
    {
        $mode = (string) ($data['client_mode'] ?? 'guest');

        if ($mode === 'existing') {
            $clientId = (int) ($data['client_id'] ?? 0);

            if ($clientId === 0) {
                throw new RuntimeException('Выберите клиента из списка.');
            }

            $client = ClientModel::query()->findOrFail($clientId);

            return [
                $clientId,
                new ClientSnapshot([
                    'full_name' => $client->full_name,
                    'phone' => $client->phone,
                ]),
            ];
        }

        $fullName = trim((string) ($data['client_full_name'] ?? ''));
        $phone = trim((string) ($data['client_phone'] ?? ''));

        if ($fullName === '' || $phone === '') {
            throw new RuntimeException('Укажите имя и телефон клиента.');
        }

        return [
            null,
            new ClientSnapshot([
                'full_name' => $fullName,
                'phone' => $phone,
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function resolveEquipmentId(array $data): ?int
    {
        $mode = (string) ($data['equipment_mode'] ?? 'existing');

        if ($mode === 'existing') {
            $equipmentId = (int) ($data['equipment_id'] ?? 0);

            return $equipmentId > 0 ? $equipmentId : null;
        }

        $name = trim((string) ($data['equipment_name'] ?? ''));

        if ($name === '') {
            return null;
        }

        $equipment = app(RegisterEquipmentHandler::class)->handle(new RegisterEquipmentCommand(
            name: $name,
            brand: filled($data['equipment_brand'] ?? null) ? (string) $data['equipment_brand'] : null,
            model: filled($data['equipment_model'] ?? null) ? (string) $data['equipment_model'] : null,
        ));

        $equipmentId = $equipment->id();

        return $equipmentId !== null ? $equipmentId : null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<OrderTool>
     */
    private static function resolveTools(array $data): array
    {
        $tools = [];

        foreach ($data['tools'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }

            $name = trim((string) ($row['name'] ?? ''));
            $toolType = (string) ($row['tool_type'] ?? '');
            $quantity = (int) ($row['quantity'] ?? 0);

            if ($name === '' || $toolType === '' || $quantity < 1) {
                continue;
            }

            $tools[] = new OrderTool(
                id: null,
                toolType: $toolType,
                quantity: $quantity,
                name: $name,
            );
        }

        return $tools;
    }
}
