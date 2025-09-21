<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClientFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем филиал для тестов
        Branch::factory()->create([
            'name' => 'Тестовый филиал',
            'address' => 'Тестовый адрес',
            'phone' => '+7 (999) 123-45-67',
            'is_active' => true,
        ]);
    }

    /**
     * Тест регистрации нового клиента
     */
    public function test_client_registration()
    {
        $clientData = [
            'full_name' => 'Иван Иванов',
            'phone' => '+7 (999) 123-45-67',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $clientData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'full_name',
                'phone',
                'created_at',
                'updated_at',
            ]);

        $this->assertDatabaseHas('clients', [
            'full_name' => 'Иван Иванов',
            'phone' => '+7 (999) 123-45-67',
        ]);

        // Проверяем, что создался бонусный аккаунт
        $client = Client::where('phone', '+7 (999) 123-45-67')->first();
        $this->assertNotNull($client->bonusAccount);
        $this->assertEquals(0, $client->bonusAccount->balance);
    }

    /**
     * Тест авторизации клиента
     */
    public function test_client_login()
    {
        // Создаем клиента
        $client = Client::factory()->create([
            'phone' => '+7 (999) 123-45-67',
            'password' => Hash::make('password123'),
        ]);

        $loginData = [
            'phone' => '+7 (999) 123-45-67',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'client' => [
                    'id',
                    'full_name',
                    'phone',
                ],
            ]);

        $this->assertNotEmpty($response->json('token'));
    }

    /**
     * Тест создания заявки на заточку инструмента
     */
    public function test_create_sharpening_order()
    {
        $client = Client::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;

        $orderData = [
            'client_phone' => $client->phone,
            'client_name' => $client->full_name,
            'service_type' => Order::TYPE_SHARPENING,
            'problem_description' => 'Нужно заточить кухонные ножи',
            'urgency' => Order::URGENCY_NORMAL,
        ];

        $response = $this->postJson('/api/order/create', $orderData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'order' => [
                    'id',
                    'type',
                    'status',
                    'urgency',
                    'problem_description',
                    'order_number',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('orders', [
            'client_id' => $client->id,
            'type' => Order::TYPE_SHARPENING,
            'status' => Order::STATUS_NEW,
            'problem_description' => 'Нужно заточить кухонные ножи',
        ]);
    }

    /**
     * Тест создания заявки на ремонт инструмента
     */
    public function test_create_repair_order()
    {
        $client = Client::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;

        $orderData = [
            'client_phone' => $client->phone,
            'client_name' => $client->full_name,
            'service_type' => Order::TYPE_REPAIR,
            'problem_description' => 'Сломался блендер, не включается',
            'urgency' => Order::URGENCY_URGENT,
        ];

        $response = $this->postJson('/api/order/create', $orderData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'order' => [
                    'id',
                    'type',
                    'status',
                    'urgency',
                    'problem_description',
                    'order_number',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('orders', [
            'client_id' => $client->id,
            'type' => Order::TYPE_REPAIR,
            'status' => Order::STATUS_NEW,
            'urgency' => Order::URGENCY_URGENT,
            'problem_description' => 'Сломался блендер, не включается',
        ]);
    }

    /**
     * Тест получения информации о клиенте
     */
    public function test_get_client_info()
    {
        $client = Client::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/client/self');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'client' => [
                    'id',
                    'full_name',
                    'phone',
                    'email',
                    'telegram',
                    'birth_date',
                    'delivery_address',
                ],
                'bonusAccount' => [
                    'id',
                    'balance',
                ],
            ]);
    }

    /**
     * Тест получения заказов клиента
     */
    public function test_get_client_orders()
    {
        $client = Client::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;

        // Создаем несколько заказов
        $order1 = Order::factory()->create([
            'client_id' => $client->id,
            'type' => Order::TYPE_SHARPENING,
        ]);

        $order2 = Order::factory()->create([
            'client_id' => $client->id,
            'type' => Order::TYPE_REPAIR,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/client/orders-get');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'orders' => [
                    '*' => [
                        'id',
                        'type',
                        'status',
                        'urgency',
                        'order_number',
                        'created_at',
                    ],
                ],
            ]);

        $this->assertCount(2, $response->json('orders'));
    }

    /**
     * Тест обновления информации о клиенте
     */
    public function test_update_client_info()
    {
        $client = Client::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;

        $updateData = [
            'full_name' => 'Петр Петров',
            'email' => 'petr@example.com',
            'delivery_address' => 'Новый адрес доставки',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/client/update', $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'client',
                'message',
            ]);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'full_name' => 'Петр Петров',
            'email' => 'petr@example.com',
            'delivery_address' => 'Новый адрес доставки',
        ]);
    }

    /**
     * Тест обновления только имени клиента
     */
    public function test_update_client_name_only()
    {
        $client = Client::factory()->create([
            'full_name' => 'Иван Иванов',
            'email' => 'ivan@example.com',
        ]);
        $token = $client->createToken('test-token')->plainTextToken;

        $updateData = [
            'full_name' => 'Иван Петрович Иванов',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/client/update', $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'full_name' => 'Иван Петрович Иванов',
            'email' => 'ivan@example.com', // email не должен измениться
        ]);
    }

    /**
     * Тест обновления контактных данных клиента
     */
    public function test_update_client_contact_info()
    {
        $client = Client::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;

        $updateData = [
            'phone' => '+7 (999) 888-77-66',
            'email' => 'newemail@example.com',
            'telegram' => '@newtelegram',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/client/update', $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'phone' => '+7 (999) 888-77-66',
            'email' => 'newemail@example.com',
            'telegram' => '@newtelegram',
        ]);
    }

    /**
     * Тест обновления адреса доставки клиента
     */
    public function test_update_client_delivery_address()
    {
        $client = Client::factory()->create([
            'delivery_address' => 'Старый адрес',
        ]);
        $token = $client->createToken('test-token')->plainTextToken;

        $updateData = [
            'delivery_address' => 'Новый адрес доставки, дом 123, кв. 45',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/client/update', $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'delivery_address' => 'Новый адрес доставки, дом 123, кв. 45',
        ]);
    }

    /**
     * Тест обновления даты рождения клиента
     */
    public function test_update_client_birth_date()
    {
        $client = Client::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;

        $updateData = [
            'birth_date' => '1990-05-15',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/client/update', $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
        ]);

        // Проверяем что дата обновилась
        $updatedClient = Client::find($client->id);
        $this->assertEquals('1990-05-15', $updatedClient->birth_date->format('Y-m-d'));
    }

    /**
     * Тест обновления всех данных клиента одновременно
     */
    public function test_update_all_client_data()
    {
        $client = Client::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;

        $updateData = [
            'full_name' => 'Александр Александрович Александров',
            'phone' => '+7 (999) 111-22-33',
            'email' => 'alexander@example.com',
            'telegram' => '@alexander',
            'birth_date' => '1985-12-25',
            'delivery_address' => 'Москва, ул. Ленина, д. 1, кв. 10',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/client/update', $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'full_name' => 'Александр Александрович Александров',
            'phone' => '+7 (999) 111-22-33',
            'email' => 'alexander@example.com',
            'telegram' => '@alexander',
            'delivery_address' => 'Москва, ул. Ленина, д. 1, кв. 10',
        ]);

        // Проверяем что дата обновилась
        $updatedClient = Client::find($client->id);
        $this->assertEquals('1985-12-25', $updatedClient->birth_date->format('Y-m-d'));
    }

    /**
     * Тест обновления клиента без авторизации
     */
    public function test_update_client_without_auth()
    {
        $updateData = [
            'full_name' => 'Неавторизованный пользователь',
        ];

        $response = $this->postJson('/api/client/update', $updateData);

        $response->assertStatus(401);
    }

    /**
     * Тест обновления клиента с пустыми данными
     */
    public function test_update_client_with_empty_data()
    {
        $client = Client::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/client/update', []);

        $response->assertStatus(200); // API принимает пустые данные
    }

    /**
     * Тест обновления клиента с невалидными данными
     */
    public function test_update_client_with_invalid_data()
    {
        $client = Client::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;

        $updateData = [
            'email' => 'invalid-email', // невалидный email
            'phone' => 'invalid-phone', // невалидный телефон
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/client/update', $updateData);

        // API может принимать невалидные данные, но они должны сохраниться как есть
        $response->assertStatus(200);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'email' => 'invalid-email',
            'phone' => 'invalid-phone',
        ]);
    }

    /**
     * Тест создания отзыва через API
     */
    public function test_create_review_via_api()
    {
        $client = Client::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;

        // Создаем завершенный заказ
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'status' => Order::STATUS_ISSUED,
            'type' => Order::TYPE_SHARPENING,
        ]);

        $reviewData = [
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Отличная работа! Ножи заточены как новые.',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/client/review', $reviewData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'review' => [
                    'id',
                    'client_id',
                    'order_id',
                    'rating',
                    'comment',
                    'created_at',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('reviews', [
            'client_id' => $client->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Отличная работа! Ножи заточены как новые.',
        ]);
    }

    /**
     * Тест создания отзыва для незавершенного заказа
     */
    public function test_create_review_for_incomplete_order()
    {
        $client = Client::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;

        // Создаем незавершенный заказ
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'status' => Order::STATUS_IN_WORK,
            'type' => Order::TYPE_SHARPENING,
        ]);

        $reviewData = [
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Отличная работа!',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/client/review', $reviewData);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Order must be completed to leave a review']);
    }

    /**
     * Тест создания отзыва без авторизации
     */
    public function test_create_review_without_auth()
    {
        $reviewData = [
            'order_id' => 1,
            'rating' => 5,
            'comment' => 'Отличная работа!',
        ];

        $response = $this->postJson('/api/client/review', $reviewData);

        $response->assertStatus(401);
    }

    /**
     * Тест валидации при создании отзыва
     */
    public function test_create_review_validation()
    {
        $client = Client::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;

        // Тест с пустыми данными
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/client/review', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id', 'rating', 'comment']);

        // Тест с невалидными данными
        $invalidData = [
            'order_id' => 999, // несуществующий заказ
            'rating' => 10, // рейтинг больше 5
            'comment' => str_repeat('a', 1001), // комментарий слишком длинный
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/client/review', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id', 'rating', 'comment']);
    }

    /**
     * Тест валидации при регистрации
     */
    public function test_registration_validation()
    {
        // Тест без обязательных полей
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['full_name', 'phone', 'password']);

        // Тест с неправильным подтверждением пароля
        $clientData = [
            'full_name' => 'Иван Иванов',
            'phone' => '+7 (999) 123-45-67',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ];

        $response = $this->postJson('/api/register', $clientData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password_confirmation']);
    }

    /**
     * Тест валидации при авторизации
     */
    public function test_login_validation()
    {
        // Тест с несуществующим клиентом
        $loginData = [
            'phone' => '+7 (999) 999-99-99',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(404)
            ->assertJson(['message' => 'Client not found']);

        // Тест с неправильным паролем
        $client = Client::factory()->create([
            'phone' => '+7 (999) 123-45-67',
            'password' => Hash::make('correct_password'),
        ]);

        $loginData = [
            'phone' => '+7 (999) 123-45-67',
            'password' => 'wrong_password',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid password']);
    }

    /**
     * Тест создания заказа для несуществующего клиента
     */
    public function test_create_order_for_new_client()
    {
        $orderData = [
            'client_phone' => '+7 (999) 999-99-99',
            'client_name' => 'Новый Клиент',
            'service_type' => Order::TYPE_SHARPENING,
            'problem_description' => 'Нужно заточить ножи',
        ];

        $response = $this->postJson('/api/order/create', $orderData);

        $response->assertStatus(200);

        // Проверяем, что клиент был создан
        $this->assertDatabaseHas('clients', [
            'phone' => '+7 (999) 999-99-99',
            'full_name' => 'Новый Клиент',
        ]);

        // Проверяем, что заказ был создан
        $this->assertDatabaseHas('orders', [
            'type' => Order::TYPE_SHARPENING,
            'problem_description' => 'Нужно заточить ножи',
        ]);
    }
}
