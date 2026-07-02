<?php

namespace Tests\Feature\ClientPortal;

use App\Application\ClientPortal\Command\CreateClientCommand;
use App\Application\ClientPortal\Command\ApproveReviewCommand;
use App\Application\ClientPortal\Command\LinkGuestOrdersToClientCommand;
use App\Application\ClientPortal\Command\LinkGuestOrderToClientCommand;
use App\Application\ClientPortal\Command\RegisterClientCommand;
use App\Application\ClientPortal\Command\RejectReviewCommand;
use App\Application\ClientPortal\Command\SubmitReviewCommand;
use App\Application\ClientPortal\Command\SubmitSiteLeadCommand;
use App\Application\ClientPortal\CommandHandler\CreateClientHandler;
use App\Application\ClientPortal\CommandHandler\ApproveReviewHandler;
use App\Application\ClientPortal\CommandHandler\LinkGuestOrdersToClientHandler;
use App\Application\ClientPortal\CommandHandler\LinkGuestOrderToClientHandler;
use App\Application\ClientPortal\CommandHandler\RegisterClientHandler;
use App\Application\ClientPortal\CommandHandler\RejectReviewHandler;
use App\Application\ClientPortal\CommandHandler\SubmitReviewHandler;
use App\Application\ClientPortal\CommandHandler\SubmitSiteLeadHandler;
use App\Application\ClientPortal\Query\GetClientOrdersQuery;
use App\Application\ClientPortal\Query\GetClientReviewsQuery;
use App\Application\ClientPortal\QueryHandler\GetClientOrdersQueryHandler;
use App\Application\ClientPortal\QueryHandler\GetClientReviewsQueryHandler;
use App\Application\OrderFulfillment\Command\CreateOrderCommand;
use App\Application\OrderFulfillment\CommandHandler\CreateOrderHandler;
use App\Domain\ClientPortal\Exception\ClientAlreadyRegisteredException;
use App\Domain\ClientPortal\Enum\ReviewStatus;
use App\Domain\ClientPortal\Exception\ReviewPolicyViolation;
use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use Database\Seeders\DomainSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ClientPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_заявка_с_сайта(): void
    {
        $this->seed(DomainSeeder::class);

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
        $this->seed(DomainSeeder::class);

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
        $this->seed(DomainSeeder::class);

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

    public function test_менеджер_создаёт_клиента_вручную(): void
    {
        $this->seed(DomainSeeder::class);

        $client = app(CreateClientHandler::class)->handle(new CreateClientCommand(
            phone: '+79008887766',
            fullName: 'Пётр Сидоров',
            email: 'petr@example.com',
            birthDate: '1990-05-15',
            deliveryAddress: 'ул. Ленина, 1',
        ));

        $this->assertNotNull($client->id());
        $this->assertSame('+79008887766', $client->phone());
        $this->assertSame('Пётр Сидоров', $client->fullName());
        $this->assertSame('petr@example.com', $client->email());
        $this->assertSame('1990-05-15', $client->birthDate());
        $this->assertSame('ул. Ленина, 1', $client->deliveryAddress());
        $this->assertTrue($client->requiresPasswordSet());
    }

    public function test_нельзя_создать_клиента_с_занятым_телефоном(): void
    {
        $this->seed(DomainSeeder::class);

        app(CreateClientHandler::class)->handle(new CreateClientCommand(
            phone: '+79007654321',
            fullName: 'Первый клиент',
        ));

        $this->expectException(ClientAlreadyRegisteredException::class);

        app(CreateClientHandler::class)->handle(new CreateClientCommand(
            phone: '+79007654321',
            fullName: 'Второй клиент',
        ));
    }

    public function test_регистрация_лк_и_привязка_гостевых_заказов(): void
    {
        $this->seed(DomainSeeder::class);

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

    public function test_менеджер_привязывает_гостевой_заказ_с_другим_телефоном(): void
    {
        $this->seed(DomainSeeder::class);

        $guestOrder = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['sharpening'],
            clientSnapshot: new ClientSnapshot(['full_name' => 'Пётр', 'phone' => '+79001110000']),
        ));

        $client = app(RegisterClientHandler::class)->handle(new RegisterClientCommand(
            phone: '+79009998877',
            fullName: 'Иван Петров',
            password: 'password123',
        ));

        app(LinkGuestOrderToClientHandler::class)->handle(new LinkGuestOrderToClientCommand(
            clientId: $client->id() ?? 0,
            orderId: $guestOrder->id() ?? 0,
        ));

        $active = app(GetClientOrdersQueryHandler::class)->handle(new GetClientOrdersQuery(
            clientId: $client->id() ?? 0,
            history: false,
        ));

        $this->assertCount(1, $active['items']);
        $this->assertSame($guestOrder->id(), $active['items'][0]->id());
    }

    public function test_отзыв_только_после_выдачи(): void
    {
        $this->seed(DomainSeeder::class);

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
        $this->seed(DomainSeeder::class);

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
        $issued = OrderModel::query()
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

    public function test_менеджер_получает_отзывы_клиента(): void
    {
        $this->seed(DomainSeeder::class);

        $client = app(RegisterClientHandler::class)->handle(new RegisterClientCommand(
            phone: '+79007778899',
            fullName: 'Светлана',
            password: 'password123',
        ));

        $clientId = $client->id();
        $this->assertNotNull($clientId);

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['sharpening'],
            clientId: $clientId,
            clientSnapshot: new ClientSnapshot(['full_name' => 'Светлана', 'phone' => '+79007778899']),
        ));

        $orderId = $order->id();
        $this->assertNotNull($orderId);

        $issued = OrderModel::query()->findOrFail($orderId);
        $issued->status = OrderStatus::Issued;
        $issued->save();

        $review = app(SubmitReviewHandler::class)->handle(new SubmitReviewCommand(
            clientId: $clientId,
            orderId: $orderId,
            rating: 4,
            comment: 'Хорошо',
        ));

        $reviews = app(GetClientReviewsQueryHandler::class)->handle(new GetClientReviewsQuery($clientId));

        $this->assertCount(1, $reviews);
        $this->assertSame($review->id(), $reviews[0]->id());
        $this->assertSame(ReviewStatus::Pending, $reviews[0]->status());
    }

    public function test_менеджер_публикует_отзыв_клиента(): void
    {
        $this->seed(DomainSeeder::class);

        $client = app(RegisterClientHandler::class)->handle(new RegisterClientCommand(
            phone: '+79008889900',
            fullName: 'Дмитрий',
            password: 'password123',
        ));

        $clientId = $client->id();
        $this->assertNotNull($clientId);

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['sharpening'],
            clientId: $clientId,
            clientSnapshot: new ClientSnapshot(['full_name' => 'Дмитрий', 'phone' => '+79008889900']),
        ));

        $orderId = $order->id();
        $this->assertNotNull($orderId);

        $issued = OrderModel::query()->findOrFail($orderId);
        $issued->status = OrderStatus::Issued;
        $issued->save();

        $review = app(SubmitReviewHandler::class)->handle(new SubmitReviewCommand(
            clientId: $clientId,
            orderId: $orderId,
            rating: 5,
        ));

        $reviewId = $review->id();
        $this->assertNotNull($reviewId);

        $approved = app(ApproveReviewHandler::class)->handle(new ApproveReviewCommand(
            reviewId: $reviewId,
            clientId: $clientId,
        ));

        $this->assertSame(ReviewStatus::Approved, $approved->status());
    }

    public function test_менеджер_отклоняет_отзыв_клиента(): void
    {
        $this->seed(DomainSeeder::class);

        $client = app(RegisterClientHandler::class)->handle(new RegisterClientCommand(
            phone: '+79009990011',
            fullName: 'Елена',
            password: 'password123',
        ));

        $clientId = $client->id();
        $this->assertNotNull($clientId);

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['sharpening'],
            clientId: $clientId,
            clientSnapshot: new ClientSnapshot(['full_name' => 'Елена', 'phone' => '+79009990011']),
        ));

        $orderId = $order->id();
        $this->assertNotNull($orderId);

        $issued = OrderModel::query()->findOrFail($orderId);
        $issued->status = OrderStatus::Issued;
        $issued->save();

        $review = app(SubmitReviewHandler::class)->handle(new SubmitReviewCommand(
            clientId: $clientId,
            orderId: $orderId,
            rating: 2,
            comment: 'Плохо',
        ));

        $reviewId = $review->id();
        $this->assertNotNull($reviewId);

        $rejected = app(RejectReviewHandler::class)->handle(new RejectReviewCommand(
            reviewId: $reviewId,
            clientId: $clientId,
        ));

        $this->assertSame(ReviewStatus::Rejected, $rejected->status());
    }

    public function test_нельзя_модерировать_отзыв_другого_клиента(): void
    {
        $this->seed(DomainSeeder::class);

        $client = app(RegisterClientHandler::class)->handle(new RegisterClientCommand(
            phone: '+79001112255',
            fullName: 'Клиент А',
            password: 'password123',
        ));

        $otherClient = app(RegisterClientHandler::class)->handle(new RegisterClientCommand(
            phone: '+79001112266',
            fullName: 'Клиент Б',
            password: 'password123',
        ));

        $clientId = $client->id();
        $otherClientId = $otherClient->id();
        $this->assertNotNull($clientId);
        $this->assertNotNull($otherClientId);

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['sharpening'],
            clientId: $clientId,
            clientSnapshot: new ClientSnapshot(['full_name' => 'Клиент А', 'phone' => '+79001112255']),
        ));

        $orderId = $order->id();
        $this->assertNotNull($orderId);

        $issued = OrderModel::query()->findOrFail($orderId);
        $issued->status = OrderStatus::Issued;
        $issued->save();

        $review = app(SubmitReviewHandler::class)->handle(new SubmitReviewCommand(
            clientId: $clientId,
            orderId: $orderId,
            rating: 5,
        ));

        $reviewId = $review->id();
        $this->assertNotNull($reviewId);

        $this->expectException(ReviewPolicyViolation::class);

        app(ApproveReviewHandler::class)->handle(new ApproveReviewCommand(
            reviewId: $reviewId,
            clientId: $otherClientId,
        ));
    }
}
