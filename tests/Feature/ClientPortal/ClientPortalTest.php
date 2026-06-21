<?php

namespace Tests\Feature\ClientPortal;

use App\Application\ClientPortal\Command\LinkGuestOrdersToClientCommand;
use App\Application\ClientPortal\Command\RegisterClientCommand;
use App\Application\ClientPortal\Command\SubmitReviewCommand;
use App\Application\ClientPortal\Command\SubmitSiteLeadCommand;
use App\Application\ClientPortal\CommandHandler\LinkGuestOrdersToClientHandler;
use App\Application\ClientPortal\CommandHandler\RegisterClientHandler;
use App\Application\ClientPortal\CommandHandler\SubmitReviewHandler;
use App\Application\ClientPortal\CommandHandler\SubmitSiteLeadHandler;
use App\Application\ClientPortal\Query\GetClientOrdersQuery;
use App\Application\ClientPortal\QueryHandler\GetClientOrdersQueryHandler;
use App\Application\OrderFulfillment\Command\CreateOrderCommand;
use App\Application\OrderFulfillment\CommandHandler\CreateOrderHandler;
use App\Domain\ClientPortal\Exception\ReviewPolicyViolation;
use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ClientPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_заявка_с_сайта(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $lead = app(SubmitSiteLeadHandler::class)->handle(new SubmitSiteLeadCommand(
            fullName: 'Анна',
            phone: '+79003334455',
            serviceTypes: ['sharpening'],
            comment: 'Ножницы',
            intakeData: [
                'tool_type' => 'manicure',
                'tools_count' => 1,
            ],
        ));

        $this->assertNotNull($lead->id());
        $this->assertFalse($lead->isConverted());
        $this->assertSame('manicure', $lead->intakeData()['tool_type']);
        $this->assertSame('Ножницы', $lead->comment());
    }

    public function test_api_отклоняет_заявку_без_intake_data(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $response = $this->postJson('/api/leads', [
            'full_name' => 'Тест',
            'phone' => '+79001112233',
            'service_types' => ['sharpening'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['intake_data']);
    }

    public function test_api_отклоняет_заточку_без_типа_инструмента(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $response = $this->postJson('/api/leads', [
            'full_name' => 'Тест',
            'phone' => '+79001112244',
            'service_types' => ['sharpening'],
            'intake_data' => [
                'tools_count' => 2,
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['intake_data.tool_type']);
    }

    public function test_регистрация_лк_и_привязка_гостевых_заказов(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $guestOrder = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['sharpening'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Иван', 'phone' => '+79009998877']),
        ));

        $client = app(RegisterClientHandler::class)->handle(new RegisterClientCommand(
            phone: '+79009998877',
            fullName: 'Иван Петров',
            password: 'password123',
        ));

        $count = app(LinkGuestOrdersToClientHandler::class)->handle(
            new LinkGuestOrdersToClientCommand($client->id() ?? 0),
        );

        $this->assertSame(1, $count);

        $active = app(GetClientOrdersQueryHandler::class)->handle(new GetClientOrdersQuery(
            clientId: $client->id() ?? 0,
            history: false,
        ));

        $this->assertCount(1, $active['items']);
        $this->assertSame($guestOrder->id(), $active['items'][0]->id());
    }

    public function test_отзыв_только_после_выдачи(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $client = app(RegisterClientHandler::class)->handle(new RegisterClientCommand(
            phone: '+79005556677',
            fullName: 'Мария',
            password: 'password123',
        ));

        $clientId = $client->id();
        $this->assertNotNull($clientId);

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['sharpening'],
            clientId: $clientId,
            clientSnapshot: new ClientSnapshot(['full_name' => 'Мария', 'phone' => '+79005556677']),
        ));

        $orderId = $order->id();
        $this->assertNotNull($orderId);

        $this->expectException(ReviewPolicyViolation::class);

        app(SubmitReviewHandler::class)->handle(new SubmitReviewCommand(
            clientId: $clientId,
            orderId: $orderId,
            rating: 5,
        ));
    }

    public function test_отзыв_после_выдачи(): void
    {
        $this->seed(\Database\Seeders\DomainSeeder::class);

        $client = app(RegisterClientHandler::class)->handle(new RegisterClientCommand(
            phone: '+79006667788',
            fullName: 'Олег',
            password: 'password123',
        ));

        $clientId = $client->id();
        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['sharpening'],
            clientId: $clientId,
            clientSnapshot: new ClientSnapshot(['full_name' => 'Олег', 'phone' => '+79006667788']),
        ));

        $orderId = $order->id();
        $this->assertNotNull($orderId);

        // Прямое выставление issued для теста отзыва (минуя полный lifecycle)
        $issued = \App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel::query()
            ->findOrFail($orderId);
        $issued->status = OrderStatus::Issued;
        $issued->save();

        $review = app(SubmitReviewHandler::class)->handle(new SubmitReviewCommand(
            clientId: $clientId ?? 0,
            orderId: $orderId,
            rating: 5,
            comment: 'Отлично!',
        ));

        $this->assertSame(5, $review->rating());
    }
}
