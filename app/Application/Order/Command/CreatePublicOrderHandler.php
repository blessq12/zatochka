<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\CreateOrderItemDTO;
use App\Application\Order\Port\ClientIdentityPort;
use App\Application\Order\Port\PublicRepairEquipmentPort;
use App\Application\Shared\UnitOfWork;
use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderSource;
use App\Domain\Order\VO\OrderUrgency;
use App\Domain\Order\VO\SharpeningToolType;
use App\Shared\Domain\DomainException;

final readonly class CreatePublicOrderHandler
{
    public function __construct(
        private ClientIdentityPort $clients,
        private PublicRepairEquipmentPort $repairEquipment,
        private CreateOrderHandler $createOrder,
        private UnitOfWork $unitOfWork,
    ) {}

    /**
     * @return array{order_id: string, client_id: int, message: string}
     */
    public function handle(CreatePublicOrderCommand $command): array
    {
        return $this->unitOfWork->execute(function () use ($command): array {
            $serviceType = OrderServiceType::tryFrom($command->serviceType)
                ?? throw new DomainException('Unknown order service type.');

            $clientId = $this->clients->resolveOrRegister(
                $command->authenticatedClientId,
                $command->phone,
                $command->fullName,
                $command->needsDelivery ? $command->deliveryAddress : null,
            );

            $intake = $command->intake ?? [];
            $items = match ($serviceType) {
                OrderServiceType::Sharpening => $this->sharpeningItems($intake),
                OrderServiceType::Repair => $this->repairItems($clientId, $intake, $command->comment),
            };

            $urgency = match ($serviceType) {
                OrderServiceType::Repair => $this->mapUrgency((string) ($intake['urgency_type'] ?? 'standard')),
                OrderServiceType::Sharpening => OrderUrgency::Normal->value,
            };

            $clientComment = $this->buildClientComment($serviceType, $intake, $command->comment);

            $orderId = OrderId::generate()->value;

            $this->createOrder->handle(new CreateOrderCommand(
                $orderId,
                $clientId,
                '0.00',
                $items,
                $serviceType->value,
                OrderBillingType::Paid->value,
                $urgency,
                $command->needsDelivery,
                null,
                null,
                'RUB',
                null,
                $clientComment,
                OrderSource::Website->value,
            ));

            return [
                'order_id' => $orderId,
                'client_id' => $clientId,
                'message' => 'Заказ создан. Менеджер свяжется с вами.',
            ];
        });
    }

    /**
     * @param array<string, mixed> $intake
     * @return list<CreateOrderItemDTO>
     */
    private function sharpeningItems(array $intake): array
    {
        $frontendType = (string) ($intake['tool_type'] ?? '');
        [$toolType, $toolName] = $this->mapSharpeningTool($frontendType);

        $quantity = (int) ($intake['tools_count'] ?? 0);
        if ($quantity < 1) {
            throw new DomainException('Tools count must be at least 1.');
        }

        return [
            new CreateOrderItemDTO(
                toolName: $toolName,
                toolType: $toolType,
                quantity: $quantity,
            ),
        ];
    }

    /**
     * @param array<string, mixed> $intake
     * @return list<CreateOrderItemDTO>
     */
    private function repairItems(int $clientId, array $intake, ?string $comment): array
    {
        $deviceName = $this->nullableString($intake['device_name'] ?? null)
            ?? throw new DomainException('Device name is required for repair.');
        $equipmentType = $this->nullableString($intake['equipment_type'] ?? null)
            ?? throw new DomainException('Equipment type is required for repair.');

        $problem = $this->nullableString($intake['problem_description'] ?? null)
            ?? $this->nullableString($comment);

        $equipmentId = $this->repairEquipment->ensureForClient(
            $clientId,
            $deviceName,
            $equipmentType,
            $problem,
        );

        return [
            new CreateOrderItemDTO(clientEquipmentId: $equipmentId),
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function mapSharpeningTool(string $frontendType): array
    {
        return match ($frontendType) {
            'manicure' => [SharpeningToolType::ManicureTool->value, SharpeningToolType::ManicureTool->label()],
            'hair' => [SharpeningToolType::HairdressingScissors->value, SharpeningToolType::HairdressingScissors->label()],
            'grooming', 'barber' => [SharpeningToolType::Scissors->value, SharpeningToolType::Scissors->label()],
            'other' => [SharpeningToolType::Other->value, SharpeningToolType::Other->label()],
            default => SharpeningToolType::tryFrom($frontendType) !== null
                ? [$frontendType, SharpeningToolType::from($frontendType)->label()]
                : throw new DomainException('Unknown sharpening tool type.'),
        };
    }

    /**
     * @param array<string, mixed> $intake
     */
    private function buildClientComment(
        OrderServiceType $serviceType,
        array $intake,
        ?string $comment,
    ): ?string {
        $comment = $this->nullableString($comment)
            ?? $this->nullableString($intake['extra_comment'] ?? null);

        if ($serviceType === OrderServiceType::Sharpening) {
            return $comment;
        }

        $problem = $this->nullableString($intake['problem_description'] ?? null);
        $parts = array_values(array_filter([$problem, $comment], static fn (?string $part): bool => $part !== null));

        if ($parts === []) {
            return null;
        }

        return implode("\n\n", $parts);
    }

    private function mapUrgency(string $urgencyType): string
    {
        return match ($urgencyType) {
            'urgent', 'express' => OrderUrgency::Urgent->value,
            'standard', 'normal', '' => OrderUrgency::Normal->value,
            default => OrderUrgency::tryFrom($urgencyType)?->value
                ?? throw new DomainException('Unknown order urgency.'),
        };
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }
}
