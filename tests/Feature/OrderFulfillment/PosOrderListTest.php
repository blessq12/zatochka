<?php

namespace Tests\Feature\OrderFulfillment;

use App\Application\Equipment\Command\RegisterEquipmentCommand;
use App\Application\Equipment\CommandHandler\RegisterEquipmentHandler;
use App\Application\OrderFulfillment\Command\AddWorkCommand;
use App\Application\OrderFulfillment\Command\AssignMasterToOrderCommand;
use App\Application\OrderFulfillment\Command\CreateOrderCommand;
use App\Application\OrderFulfillment\Command\LinkEquipmentToOrderCommand;
use App\Application\OrderFulfillment\Command\MarkOrderReadyCommand;
use App\Application\OrderFulfillment\Command\MarkOrderWaitingForPartsCommand;
use App\Application\OrderFulfillment\Command\TakeOrderToWorkCommand;
use App\Application\OrderFulfillment\CommandHandler\AddWorkHandler;
use App\Application\OrderFulfillment\CommandHandler\AssignMasterToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\CreateOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\LinkEquipmentToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\MarkOrderReadyHandler;
use App\Application\OrderFulfillment\CommandHandler\MarkOrderWaitingForPartsHandler;
use App\Application\OrderFulfillment\CommandHandler\TakeOrderToWorkHandler;
use App\Domain\OrderFulfillment\Entity\OrderTool;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use Database\Seeders\IdentitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PosOrderListTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{token: string, master: UserModel} */
    private function posAuth(): array
    {
        $master = UserModel::query()->where('email', IdentitySeeder::MASTER_EMAIL)->firstOrFail();

        $login = $this->postJson('/api/pos/login', [
            'email' => IdentitySeeder::MASTER_EMAIL,
            'password' => IdentitySeeder::DEMO_PASSWORD,
        ])->assertOk();

        return [
            'token' => $login->json('token'),
            'master' => $master,
        ];
    }

    public function test_pos_list_ready_содержит_обогащённые_поля_ремонта(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        ['token' => $token, 'master' => $master] = $this->posAuth();

        $equipment = app(RegisterEquipmentHandler::class)->handle(new RegisterEquipmentCommand(
            name: 'Аппарат Wahl',
            serialNumbers: ['SN-1'],
            brand: 'Wahl',
            model: 'Magic Clip',
        ));

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['repair'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Иван Петров', 'phone' => '+79001112233']),
            problemDescription: 'Не включается после падения',
            needsDelivery: true,
        ));

        $orderId = $order->id();

        app(AssignMasterToOrderHandler::class)->handle(new AssignMasterToOrderCommand($orderId, $master->id));
        app(LinkEquipmentToOrderHandler::class)->handle(new LinkEquipmentToOrderCommand($orderId, $equipment->id()));
        app(TakeOrderToWorkHandler::class)->handle(new TakeOrderToWorkCommand($orderId, $master->id));
        app(AddWorkHandler::class)->handle(new AddWorkCommand(
            orderId: $orderId,
            masterId: $master->id,
            description: 'Замена кнопки',
        ));
        app(MarkOrderReadyHandler::class)->handle(new MarkOrderReadyCommand($orderId, $master->id));

        $response = $this->getJson('/api/pos/orders?status=completed', [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk();

        $item = collect($response->json('data'))->firstWhere('id', $orderId);

        $this->assertNotNull($item);
        $this->assertSame('Иван Петров', $item['client_name']);
        $this->assertFalse($item['is_warranty']);
        $this->assertSame('Ремонт', $item['service_type_label']);
        $this->assertSame(1, $item['works_count']);
        $this->assertTrue($item['needs_delivery']);
        $this->assertArrayHasKey('subject_line', $item);
        $this->assertArrayHasKey('equipment_summary', $item);
        $this->assertArrayHasKey('problem_excerpt', $item);
        $this->assertArrayHasKey('ready_at', $item);
        $this->assertArrayHasKey('tools_summary', $item);
        $this->assertStringContainsString('Wahl', $item['equipment_summary']);
        $this->assertStringContainsString('Не включается', $item['subject_line']);
        $this->assertNotNull($item['ready_at']);

        $response->assertJsonStructure([
            'meta' => ['total', 'page', 'per_page'],
        ]);
    }

    public function test_pos_list_active_содержит_сводку_по_заточке(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        ['token' => $token, 'master' => $master] = $this->posAuth();

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['sharpening'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Мария', 'phone' => '+79002223344']),
            tools: [
                new OrderTool(null, 'knife', 2, 'Профи'),
                new OrderTool(null, 'scissors', 1, null),
            ],
        ));

        $orderId = $order->id();

        app(AssignMasterToOrderHandler::class)->handle(new AssignMasterToOrderCommand($orderId, $master->id));
        app(TakeOrderToWorkHandler::class)->handle(new TakeOrderToWorkCommand($orderId, $master->id));

        $response = $this->getJson('/api/pos/orders?status=active', [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk();

        $items = collect($response->json('data'));
        $item = $items->firstWhere('id', $orderId);

        $this->assertNotNull($item);
        $this->assertSame('Заточка', $item['service_type_label']);
        $this->assertSame('Нож', $item['tools_summary'][0]['tool_type_label']);
        $this->assertSame('Профи', $item['tools_summary'][0]['name']);
        $this->assertSame(2, $item['tools_summary'][0]['quantity']);
        $this->assertStringContainsString('2×', $item['subject_line']);
        $this->assertStringContainsString('Профи', $item['subject_line']);
    }

    public function test_pos_нельзя_завершить_заказ_из_ожидания_запчастей(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        ['token' => $token, 'master' => $master] = $this->posAuth();

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['repair'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Тест', 'phone' => '+79001112233']),
        ));

        $orderId = $order->id();

        app(AssignMasterToOrderHandler::class)->handle(new AssignMasterToOrderCommand($orderId, $master->id));
        app(TakeOrderToWorkHandler::class)->handle(new TakeOrderToWorkCommand($orderId, $master->id));
        app(AddWorkHandler::class)->handle(new AddWorkCommand(
            orderId: $orderId,
            masterId: $master->id,
            description: 'Диагностика',
        ));
        app(MarkOrderWaitingForPartsHandler::class)->handle(
            new MarkOrderWaitingForPartsCommand($orderId, $master->id)
        );

        $this->postJson("/api/pos/orders/{$orderId}/mark-ready", [], [
            'Authorization' => 'Bearer '.$token,
        ])->assertStatus(422);
    }
}
