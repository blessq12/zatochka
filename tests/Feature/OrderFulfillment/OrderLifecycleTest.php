<?php

namespace Tests\Feature\OrderFulfillment;

use App\Application\OrderFulfillment\Command\AddWorkCommand;
use App\Application\OrderFulfillment\Command\AssignMasterToOrderCommand;
use App\Application\OrderFulfillment\Command\CreateOrderCommand;
use App\Application\OrderFulfillment\Command\IssueOrderCommand;
use App\Application\OrderFulfillment\Command\MarkOrderReadyCommand;
use App\Application\OrderFulfillment\Command\ReturnOrderForReworkCommand;
use App\Application\OrderFulfillment\Command\TakeOrderToWorkCommand;
use App\Application\OrderFulfillment\CommandHandler\AddWorkHandler;
use App\Application\OrderFulfillment\CommandHandler\AssignMasterToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\CreateOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\IssueOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\MarkOrderReadyHandler;
use App\Application\OrderFulfillment\CommandHandler\ReturnOrderForReworkHandler;
use App\Application\OrderFulfillment\CommandHandler\TakeOrderToWorkHandler;
use App\Domain\OrderFulfillment\Entity\OrderTool;
use App\Domain\OrderFulfillment\Enum\OrderSource;
use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Domain\OrderFulfillment\Event\OrderCreated;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;
use Database\Seeders\IdentitySeeder;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class OrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_заказ_проходит_цикл_new_issued(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $master = UserModel::query()->where('email', IdentitySeeder::MASTER_EMAIL)->firstOrFail();

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['sharpening'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Тест', 'phone' => '+79001112233']),
            tools: [new OrderTool(null, 'manicure', 1, 'Ножницы')],
        ));

        $orderId = $order->id();
        $this->assertNotNull($orderId);
        $this->assertSame(OrderStatus::New, $order->status());

        $order = app(AssignMasterToOrderHandler::class)->handle(new AssignMasterToOrderCommand(
            orderId: $orderId,
            masterId: $master->id,
        ));
        $this->assertSame($master->id, $order->masterId());

        $order = app(TakeOrderToWorkHandler::class)->handle(new TakeOrderToWorkCommand(
            orderId: $orderId,
            masterId: $master->id,
        ));
        $this->assertSame(OrderStatus::InWork, $order->status());

        $order = app(AddWorkHandler::class)->handle(new AddWorkCommand(
            orderId: $orderId,
            masterId: $master->id,
            description: 'Заточка ножниц',
        ));
        $this->assertCount(1, $order->works());

        $order = app(MarkOrderReadyHandler::class)->handle(new MarkOrderReadyCommand(
            orderId: $orderId,
            masterId: $master->id,
        ));
        $this->assertSame(OrderStatus::Ready, $order->status());

        $order = app(IssueOrderHandler::class)->handle(new IssueOrderCommand($orderId));
        $this->assertSame(OrderStatus::Issued, $order->status());
    }

    public function test_создание_оператором_публикует_order_created(): void
    {
        Event::fake([OrderCreated::class]);

        $this->seed(\Database\Seeders\DomainSeeder::class);

        $master = UserModel::query()->where('email', IdentitySeeder::MASTER_EMAIL)->firstOrFail();
        $manager = UserModel::query()->where('email', IdentitySeeder::MANAGER_EMAIL)->firstOrFail();

        app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['diagnosis'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Клиент', 'phone' => '+79001112233']),
            masterId: $master->id,
            managerId: $manager->id,
        ));

        Event::assertDispatched(OrderCreated::class, function (OrderCreated $event): bool {
            return $event->order->source() === OrderSource::Manual
                && $event->order->status() === OrderStatus::New;
        });
    }

    public function test_менеджер_возвращает_готовый_заказ_на_доработку(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $master = UserModel::query()->where('email', IdentitySeeder::MASTER_EMAIL)->firstOrFail();
        $manager = UserModel::query()->where('email', IdentitySeeder::MANAGER_EMAIL)->firstOrFail();

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['sharpening'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Тест', 'phone' => '+79001112233']),
            tools: [new OrderTool(null, 'manicure', 1, 'Ножницы')],
        ));

        $orderId = $order->id();
        $this->assertNotNull($orderId);

        app(AssignMasterToOrderHandler::class)->handle(new AssignMasterToOrderCommand($orderId, $master->id));
        app(TakeOrderToWorkHandler::class)->handle(new TakeOrderToWorkCommand($orderId, $master->id));
        app(AddWorkHandler::class)->handle(new AddWorkCommand(
            orderId: $orderId,
            masterId: $master->id,
            description: 'Заточка',
        ));
        app(MarkOrderReadyHandler::class)->handle(new MarkOrderReadyCommand($orderId, $master->id));

        $order = app(ReturnOrderForReworkHandler::class)->handle(new ReturnOrderForReworkCommand(
            orderId: $orderId,
            managerId: $manager->id,
            feedback: 'Подправить угол заточки',
        ));

        $this->assertSame(OrderStatus::InWork, $order->status());
        $this->assertSame('Подправить угол заточки', $order->reworkFeedback());
        $this->assertSame($manager->id, $order->reworkReturnedBy());
        $this->assertNull($order->readyAt());

        $order = app(MarkOrderReadyHandler::class)->handle(new MarkOrderReadyCommand($orderId, $master->id));

        $this->assertSame(OrderStatus::Ready, $order->status());
        $this->assertNull($order->reworkFeedback());
    }
}
