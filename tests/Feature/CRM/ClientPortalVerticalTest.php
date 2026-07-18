<?php

namespace Tests\Feature\CRM;

use App\Application\Order\Command\CreateOrderCommand;
use App\Application\Order\Command\CreateOrderHandler;
use App\Application\Order\DTO\CreateOrderItemDTO;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderStatus;
use App\Infrastructure\Order\Model\OrderModel;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class ClientPortalVerticalTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_login_profile_and_password_flow(): void
    {
        $register = $this->postJson('/api/auth/register', [
            'full_name' => 'Иван Иванов',
            'email' => 'ivan@example.com',
            'phone' => '+7 (999) 111-22-33',
            'password' => 'secret12',
            'password_confirmation' => 'secret12',
        ]);

        $register->assertCreated()->assertJsonStructure(['token']);
        $token = $register->json('token');

        $profile = $this->withToken($token)->getJson('/api/client/profile');
        $profile->assertOk()
            ->assertJsonPath('data.full_name', 'Иван Иванов')
            ->assertJsonPath('data.phone', '+7 (999) 111-22-33')
            ->assertJsonPath('data.requires_password_set', false);

        $login = $this->postJson('/api/auth/login', [
            'phone' => '+7 (999) 111-22-33',
            'password' => 'secret12',
        ]);
        $login->assertOk()->assertJsonStructure(['token']);

        $this->withToken($login->json('token'))
            ->patchJson('/api/client/profile', [
                'full_name' => 'Иван Петров',
                'birth_date' => '1990-05-01',
                'delivery_address' => 'Москва',
            ])
            ->assertOk()
            ->assertJsonPath('data.full_name', 'Иван Петров')
            ->assertJsonPath('data.birth_date', '1990-05-01')
            ->assertJsonPath('data.delivery_address', 'Москва');
    }

    public function test_master_cannot_access_client_portal(): void
    {
        $master = User::query()->create([
            'name' => 'Master',
            'email' => 'master-portal@test.local',
            'password' => 'password',
            'role' => UserRole::Master,
        ]);

        Sanctum::actingAs($master);

        $this->getJson('/api/client/profile')->assertForbidden();
    }

    public function test_client_orders_active_and_history_buckets(): void
    {
        $register = $this->postJson('/api/auth/register', [
            'full_name' => 'Клиент Заказов',
            'email' => 'orders@example.com',
            'phone' => '+7 (999) 222-33-44',
            'password' => 'secret12',
            'password_confirmation' => 'secret12',
        ])->assertCreated();

        $token = $register->json('token');
        $clientId = (int) User::query()->where('email', 'orders@example.com')->value('client_id');

        $activeOrderId = OrderId::generate()->value;
        app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            orderId: $activeOrderId,
            clientId: $clientId,
            estimatedAmount: '500.00',
            items: [
                new CreateOrderItemDTO(toolName: 'Нож', toolType: 'kitchen_knife', quantity: 1),
            ],
            serviceType: 'sharpening',
            billingType: 'paid',
            urgency: 'normal',
        ));

        $historyOrderId = OrderId::generate()->value;
        app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            orderId: $historyOrderId,
            clientId: $clientId,
            estimatedAmount: '700.00',
            items: [
                new CreateOrderItemDTO(toolName: 'Ножницы', toolType: 'scissors', quantity: 1),
            ],
            serviceType: 'sharpening',
            billingType: 'paid',
            urgency: 'normal',
        ));
        OrderModel::query()->whereKey($historyOrderId)->update([
            'status' => OrderStatus::Issued->value,
        ]);

        $active = $this->withToken($token)->getJson('/api/client/orders/active');
        $active->assertOk();
        $this->assertCount(1, $active->json('data'));
        $this->assertSame($activeOrderId, $active->json('data.0.id'));
        $this->assertSame('created', $active->json('data.0.status'));
        $this->assertSame('paid', $active->json('data.0.billing_type'));
        $this->assertSame('normal', $active->json('data.0.urgency'));
        $this->assertCount(1, $active->json('data.0.items'));
        $this->assertSame('Нож', $active->json('data.0.items.0.title'));
        $this->assertSame('Кухонный нож', $active->json('data.0.items.0.tool_type_label'));
        $this->assertSame(1, $active->json('data.0.items.0.quantity'));
        $this->assertSame('Принят', $active->json('data.0.items.0.status_label'));

        $history = $this->withToken($token)->getJson('/api/client/orders/history');
        $history->assertOk();
        $this->assertCount(1, $history->json('data'));
        $this->assertSame($historyOrderId, $history->json('data.0.id'));
        $this->assertSame('issued', $history->json('data.0.status'));
        $this->assertCount(1, $history->json('data.0.items'));
        $this->assertSame('Ножницы', $history->json('data.0.items.0.title'));
        $this->assertSame('Ножницы', $history->json('data.0.items.0.tool_type_label'));
        $this->assertFalse($history->json('data.0.review_exists'));
        $this->assertNull($history->json('data.0.review'));
    }

    public function test_guest_creates_public_sharpening_order(): void
    {
        $response = $this->postJson('/api/public/orders', [
            'full_name' => 'Гость Клиент',
            'phone' => '+7 (999) 333-44-55',
            'service_type' => 'sharpening',
            'comment' => 'нужна заточка',
            'intake_data' => [
                'tool_type' => 'manicure',
                'tools_count' => 3,
            ],
            'needs_delivery' => false,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.message', 'Заказ создан. Менеджер свяжется с вами.')
            ->assertJsonStructure(['data' => ['order_id', 'client_id']]);

        $this->assertDatabaseHas('clients', [
            'phone' => '+7 (999) 333-44-55',
            'name' => 'Гость Клиент',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $response->json('data.order_id'),
            'client_id' => $response->json('data.client_id'),
            'service_type' => 'sharpening',
            'source' => 'website',
            'client_comment' => 'нужна заточка',
            'defects' => null,
        ]);
        $this->assertDatabaseMissing('client_leads', [
            'client_id' => $response->json('data.client_id'),
        ]);
    }

    public function test_authenticated_client_creates_public_repair_order(): void
    {
        $register = $this->postJson('/api/auth/register', [
            'full_name' => 'Ремонт Клиент',
            'email' => 'repair@example.com',
            'phone' => '+7 (999) 444-55-66',
            'password' => 'secret12',
            'password_confirmation' => 'secret12',
        ]);
        $register->assertCreated();
        $token = $register->json('token');
        $clientId = (int) User::query()->where('email', 'repair@example.com')->value('client_id');

        $response = $this->withToken($token)->postJson('/api/public/orders', [
            'full_name' => 'Ремонт Клиент',
            'phone' => '+7 (999) 444-55-66',
            'service_type' => 'repair',
            'comment' => 'удобнее после 18:00',
            'needs_delivery' => true,
            'delivery_address' => 'СПб, Невский 1',
            'intake_data' => [
                'device_name' => 'Wahl Super',
                'equipment_type' => 'clipper',
                'problem_description' => 'Не включается после падения',
                'urgency_type' => 'urgent',
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.client_id', $clientId);

        $this->assertDatabaseHas('orders', [
            'id' => $response->json('data.order_id'),
            'client_id' => $clientId,
            'service_type' => 'repair',
            'source' => 'website',
            'urgency' => 'urgent',
            'delivery_required' => 1,
            'client_comment' => "Не включается после падения\n\nудобнее после 18:00",
            'defects' => null,
        ]);
        $this->assertDatabaseHas('client_equipment', [
            'client_id' => $clientId,
            'title' => 'Wahl Super',
        ]);
        $this->assertDatabaseHas('clients', [
            'id' => $clientId,
            'delivery_address' => 'СПб, Невский 1',
        ]);
    }
}
