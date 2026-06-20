<?php

namespace Tests\Feature\OrderFulfillment;

use App\Application\OrderFulfillment\Command\AddWorkCommand;
use App\Application\OrderFulfillment\Command\AssignMasterToOrderCommand;
use App\Application\OrderFulfillment\Command\CreateOrderCommand;
use App\Application\OrderFulfillment\Command\IssueOrderCommand;
use App\Application\OrderFulfillment\Command\MarkOrderReadyCommand;
use App\Application\OrderFulfillment\Command\TakeOrderToWorkCommand;
use App\Application\OrderFulfillment\CommandHandler\AddWorkHandler;
use App\Application\OrderFulfillment\CommandHandler\AssignMasterToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\CreateOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\IssueOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\MarkOrderReadyHandler;
use App\Application\OrderFulfillment\CommandHandler\TakeOrderToWorkHandler;
use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;
use Database\Seeders\IdentitySeeder;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
