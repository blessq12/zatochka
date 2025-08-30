<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Order;
use App\Models\Repair;
use App\Models\ClientBonus;
use App\Models\Review;
use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FillDemoDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-demo-data {--force : Force recreation of demo data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Заполняет приложение демо-данными: менеджерами, мастерами, клиентами, заказами и филиалами';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && $this->hasExistingData()) {
            $this->error('В базе данных уже есть данные! Используйте --force для перезаписи.');
            return 1;
        }

        $this->info('🚀 Начинаем заполнение демо-данными...');

        try {
            DB::transaction(function () {
                $this->createCompany();
                $this->createBranches();
                $this->createUsers();
                $this->createClients();
                $this->createOrders();
                $this->createReviews();
                $this->createClientBonuses();
            });

            $this->info('✅ Демо-данные успешно созданы!');
            $this->displaySummary();
        } catch (\Exception $e) {
            $this->error('❌ Ошибка при создании демо-данных: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Проверяет, есть ли уже данные в базе
     */
    private function hasExistingData(): bool
    {
        return User::count() > 0 || Client::count() > 0 || Order::count() > 0;
    }

    /**
     * Создает компанию
     */
    private function createCompany(): void
    {
        $this->info('🏢 Создаем компанию...');

        Company::firstOrCreate(
            ['name' => 'Заточка Про'],
            [
                'name' => 'Заточка Про',
                'description' => 'Профессиональная заточка и ремонт инструментов',
                'phone' => '+7 (495) 123-45-67',
                'email' => 'info@zatochka-pro.ru',
                'address' => 'г. Москва, ул. Примерная, д. 1',
                'website' => 'https://zatochka-pro.ru',
                'is_active' => true,
            ]
        );
    }

    /**
     * Создает филиалы
     */
    private function createBranches(): void
    {
        $this->info('🏪 Создаем филиалы...');

        $company = Company::first();

        $branches = [
            [
                'name' => 'Центральный офис',
                'code' => 'BR1001',
                'address' => 'г. Москва, ул. Тверская, д. 15',
                'phone' => '+7 (495) 123-45-68',
                'email' => 'central@zatochka-pro.ru',
                'working_hours' => 'Пн-Пт: 9:00-18:00, Сб: 10:00-16:00',
                'latitude' => 55.7558,
                'longitude' => 37.6176,
                'description' => 'Главный офис компании',
                'additional_data' => [
                    'manager' => 'Иванов Иван Иванович',
                    'capacity' => 150,
                    'services' => ['sharpening', 'repair', 'consultation']
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Филиал на Ленина',
                'code' => 'BR1002',
                'address' => 'г. Москва, ул. Ленина, д. 25',
                'phone' => '+7 (495) 123-45-69',
                'email' => 'lenina@zatochka-pro.ru',
                'working_hours' => 'Пн-Пт: 9:00-18:00, Сб: 10:00-16:00',
                'latitude' => 55.7500,
                'longitude' => 37.6200,
                'description' => 'Филиал в центре города',
                'additional_data' => [
                    'manager' => 'Петров Петр Петрович',
                    'capacity' => 100,
                    'services' => ['sharpening', 'repair']
                ],
                'is_active' => true,
            ]
        ];

        foreach ($branches as $branchData) {
            Branch::firstOrCreate(
                ['code' => $branchData['code']],
                array_merge($branchData, ['company_id' => $company->id])
            );
        }
    }

    /**
     * Создает пользователей (менеджеры и мастера)
     */
    private function createUsers(): void
    {
        $this->info('👥 Создаем пользователей...');

        $users = [
            // Менеджеры
            [
                'name' => 'Анна Менеджерова',
                'email' => 'anna.manager@zatochka-pro.ru',
                'password' => Hash::make('password'),
                'role' => 'manager',
            ],
            [
                'name' => 'Сергей Управляющий',
                'email' => 'sergey.manager@zatochka-pro.ru',
                'password' => Hash::make('password'),
                'role' => 'manager',
            ],
            // Мастера
            [
                'name' => 'Михаил Мастеров',
                'email' => 'mikhail.master@zatochka-pro.ru',
                'password' => Hash::make('password'),
                'role' => 'repairman',
            ],
            [
                'name' => 'Алексей Ремонтников',
                'email' => 'alexey.master@zatochka-pro.ru',
                'password' => Hash::make('password'),
                'role' => 'repairman',
            ]
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }

    /**
     * Создает клиентов
     */
    private function createClients(): void
    {
        $this->info('👤 Создаем клиентов...');

        $clients = [
            ['full_name' => 'Мария Иванова', 'phone' => '+7 (916) 111-11-11', 'telegram' => 'maria_ivanova'],
            ['full_name' => 'Дмитрий Петров', 'phone' => '+7 (916) 222-22-22', 'telegram' => 'dmitry_petrov'],
            ['full_name' => 'Елена Сидорова', 'phone' => '+7 (916) 333-33-33', 'telegram' => 'elena_sidorova'],
            ['full_name' => 'Андрей Козлов', 'phone' => '+7 (916) 444-44-44', 'telegram' => 'andrey_kozlov'],
            ['full_name' => 'Ольга Морозова', 'phone' => '+7 (916) 555-55-55', 'telegram' => 'olga_morozova'],
            ['full_name' => 'Игорь Волков', 'phone' => '+7 (916) 666-66-66', 'telegram' => 'igor_volkov'],
            ['full_name' => 'Наталья Соколова', 'phone' => '+7 (916) 777-77-77', 'telegram' => 'natalya_sokolova'],
            ['full_name' => 'Виктор Лебедев', 'phone' => '+7 (916) 888-88-88', 'telegram' => 'viktor_lebedev'],
            ['full_name' => 'Татьяна Новикова', 'phone' => '+7 (916) 999-99-99', 'telegram' => 'tatyana_novikova'],
            ['full_name' => 'Павел Медведев', 'phone' => '+7 (916) 000-00-00', 'telegram' => 'pavel_medvedev'],
        ];

        foreach ($clients as $clientData) {
            Client::firstOrCreate(
                ['phone' => $clientData['phone']],
                array_merge($clientData, [
                    'password' => Hash::make('password'),
                    'telegram_verified_at' => now(),
                    'birth_date' => fake()->dateTimeBetween('-60 years', '-18 years'),
                    'delivery_address' => fake()->address(),
                ])
            );
        }
    }

    /**
     * Создает заказы
     */
    private function createOrders(): void
    {
        $this->info('📋 Создаем заказы...');

        $clients = Client::take(7)->get();
        $branches = Branch::all();

        // Заказы на заточку
        $sharpeningOrders = [
            [
                'tool_type' => 'manicure',
                'equipment_name' => 'Маникюрные ножницы',
                'problem_description' => 'Тупие лезвия, нужна заточка',
                'work_description' => 'Заточка маникюрных ножниц на специальном станке',
            ],
            [
                'tool_type' => 'hair',
                'equipment_name' => 'Парикмахерские ножницы',
                'problem_description' => 'Ножницы не режут волосы ровно',
                'work_description' => 'Профессиональная заточка парикмахерских ножниц',
            ],
            [
                'tool_type' => 'grooming',
                'equipment_name' => 'Ножницы для груминга',
                'problem_description' => 'Требуется заточка после длительного использования',
                'work_description' => 'Заточка ножниц для груминга собак',
            ],
            [
                'tool_type' => 'hair',
                'equipment_name' => 'Ножницы для стрижки',
                'problem_description' => 'Лезвия затупились, нужна заточка',
                'work_description' => 'Заточка ножниц для стрижки волос',
            ]
        ];

        // Заказы на ремонт
        $repairOrders = [
            [
                'tool_type' => 'clipper',
                'equipment_name' => 'Машинка для стрижки Wahl',
                'problem_description' => 'Не включается, возможно проблема с двигателем',
                'work_description' => 'Диагностика и ремонт двигателя машинки',
            ],
            [
                'tool_type' => 'dryer',
                'equipment_name' => 'Фен Dyson',
                'problem_description' => 'Не греет воздух, только холодный поток',
                'work_description' => 'Ремонт нагревательного элемента фена',
            ],
            [
                'tool_type' => 'clipper',
                'equipment_name' => 'Электробритва Philips',
                'problem_description' => 'Не заряжается, не держит заряд',
                'work_description' => 'Замена аккумулятора электробритвы',
            ]
        ];

        // Создаем заказы на заточку
        foreach ($sharpeningOrders as $index => $orderData) {
            $this->createOrder($clients[$index], $orderData, 'sharpening', $branches);
        }

        // Создаем заказы на ремонт
        foreach ($repairOrders as $index => $orderData) {
            $this->createOrder($clients[$index + 4], $orderData, 'repair', $branches);
        }
    }

    /**
     * Создает заказ
     */
    private function createOrder(Client $client, array $orderData, string $serviceType, $branches): void
    {
        $totalAmount = fake()->randomFloat(2, 800, 2500);
        $discountPercent = fake()->randomFloat(2, 0, 15);
        $discountAmount = $totalAmount * ($discountPercent / 100);
        $finalPrice = $totalAmount - $discountAmount;

        $order = Order::create([
            'client_id' => $client->id,
            'order_number' => 'Z' . date('Ymd') . '-' . strtoupper(fake()->lexify('??????')),
            'service_type' => $serviceType,
            'tool_type' => $orderData['tool_type'],
            'equipment_name' => $orderData['equipment_name'],
            'problem_description' => $orderData['problem_description'],
            'work_description' => $orderData['work_description'],
            'needs_delivery' => fake()->boolean(30),
            'delivery_address' => fake()->optional()->address(),
            'urgency' => fake()->randomElement(['normal', 'urgent']),
            'needs_consultation' => fake()->boolean(20),
            'total_tools_count' => fake()->numberBetween(1, 3),
            'is_paid' => fake()->boolean(70),
            'is_ready_for_pickup' => fake()->boolean(60),
            'status' => fake()->randomElement(['new', 'confirmed', 'in_progress', 'work_completed', 'ready_for_pickup', 'delivered']),
            'total_amount' => $totalAmount,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,
            'cost_price' => $totalAmount * 0.6,
            'profit' => $finalPrice - ($totalAmount * 0.6),
            'ready_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'paid_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ]);

        // Создаем ремонт для заказов типа repair
        if ($serviceType === 'repair') {
            $this->createRepair($order, $branches->random());
        }
    }

    /**
     * Создает ремонт
     */
    private function createRepair(Order $order, Branch $branch): void
    {
        Repair::create([
            'order_id' => $order->id,
            'branch_id' => $branch->id,
            'handle_number' => 'R' . fake()->unique()->numberBetween(10000, 99999),
            'description' => fake()->randomElement([
                'Замена двигателя',
                'Ремонт электроники',
                'Замена лезвий',
                'Ремонт зарядного устройства',
                'Очистка и смазка механизмов',
                'Замена аккумулятора',
            ]),
            'cost' => fake()->randomFloat(2, 500, 2000),
            'status' => fake()->randomElement(['pending', 'in_progress', 'completed']),
        ]);
    }

    /**
     * Создает отзывы
     */
    private function createReviews(): void
    {
        $this->info('⭐ Создаем отзывы...');

        $clients = Client::all();
        $orders = Order::all();

        // Отзывы на заказы
        $orderReviews = [
            [
                'rating' => 5,
                'comment' => 'Отличная работа! Ножницы заточили как новые. Быстро и качественно.',
                'source' => 'website',
                'status' => 'approved',
            ],
            [
                'rating' => 5,
                'comment' => 'Профессиональная заточка маникюрных ножниц. Очень доволен результатом.',
                'source' => 'telegram',
                'status' => 'approved',
            ],
            [
                'rating' => 4,
                'comment' => 'Хорошая работа, но немного долго делали. Качество отличное.',
                'source' => 'website',
                'status' => 'approved',
            ],
            [
                'rating' => 5,
                'comment' => 'Починили машинку для стрижки быстро и недорого. Рекомендую!',
                'source' => 'telegram',
                'status' => 'approved',
            ],
            [
                'rating' => 3,
                'comment' => 'Работа выполнена, но можно было бы быстрее. Качество нормальное.',
                'source' => 'website',
                'status' => 'pending',
            ],
            [
                'rating' => 5,
                'comment' => 'Лучшая заточка в городе! Ножницы режут как бритва.',
                'source' => 'website',
                'status' => 'approved',
            ],
            [
                'rating' => 4,
                'comment' => 'Удобно, что есть доставка. Заточили парикмахерские ножницы отлично.',
                'source' => 'telegram',
                'status' => 'approved',
            ]
        ];

        // Создаем отзывы на заказы
        foreach ($orderReviews as $index => $reviewData) {
            if (isset($orders[$index])) {
                $this->createReview($clients->random(), $orders[$index], $reviewData);
            }
        }

        // Создаем несколько общих отзывов о сервисе
        $serviceReviews = [
            [
                'rating' => 5,
                'comment' => 'Отличный сервис! Всегда качественная работа и вежливое обслуживание.',
                'source' => 'website',
                'status' => 'approved',
            ],
            [
                'rating' => 5,
                'comment' => 'Пользуюсь услугами уже год. Ни разу не подводили. Рекомендую всем!',
                'source' => 'telegram',
                'status' => 'approved',
            ],
            [
                'rating' => 4,
                'comment' => 'Хороший сервис, цены приемлемые. Буду обращаться еще.',
                'source' => 'website',
                'status' => 'approved',
            ]
        ];

        foreach ($serviceReviews as $reviewData) {
            $this->createServiceReview($clients->random(), $reviewData);
        }
    }

    /**
     * Создает отзыв на заказ
     */
    private function createReview(Client $client, Order $order, array $reviewData): void
    {
        Review::create([
            'type' => 'order',
            'user_id' => null,
            'order_id' => $order->id,
            'entity_id' => $client->id,
            'entity_type' => Client::class,
            'rating' => $reviewData['rating'],
            'is_approved' => $reviewData['status'] === 'approved',
            'comment' => $reviewData['comment'],
            'source' => $reviewData['source'],
            'status' => $reviewData['status'],
            'reply' => fake()->optional()->sentence(),
            'metadata' => [
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'submitted_at' => fake()->dateTimeBetween('-1 month', 'now'),
            ],
        ]);
    }

    /**
     * Создает общий отзыв о сервисе
     */
    private function createServiceReview(Client $client, array $reviewData): void
    {
        Review::create([
            'type' => 'service',
            'user_id' => null,
            'order_id' => null,
            'entity_id' => $client->id,
            'entity_type' => Client::class,
            'rating' => $reviewData['rating'],
            'is_approved' => $reviewData['status'] === 'approved',
            'comment' => $reviewData['comment'],
            'source' => $reviewData['source'],
            'status' => $reviewData['status'],
            'reply' => fake()->optional()->sentence(),
            'metadata' => [
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'submitted_at' => fake()->dateTimeBetween('-1 month', 'now'),
            ],
        ]);
    }

    /**
     * Создает бонусы для клиентов
     */
    private function createClientBonuses(): void
    {
        $this->info('🎁 Создаем бонусы клиентов...');

        $clients = Client::all();

        foreach ($clients as $client) {
            $totalEarned = fake()->randomFloat(2, 100, 3000);
            $totalSpent = fake()->randomFloat(2, 0, $totalEarned * 0.7);
            $currentBalance = $totalEarned - $totalSpent;

            ClientBonus::firstOrCreate(
                ['client_id' => $client->id],
                [
                    'total_earned' => $totalEarned,
                    'total_spent' => $totalSpent,
                    'current_balance' => $currentBalance,
                    'expired_balance' => fake()->randomFloat(2, 0, 200),
                    'last_earned_at' => fake()->optional()->dateTimeBetween('-6 months', 'now'),
                    'last_spent_at' => fake()->optional()->dateTimeBetween('-3 months', 'now'),
                ]
            );
        }
    }

    /**
     * Отображает сводку созданных данных
     */
    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('📊 Сводка созданных данных:');
        $this->table(
            ['Тип', 'Количество'],
            [
                ['Компании', Company::count()],
                ['Филиалы', Branch::count()],
                ['Пользователи', User::count()],
                ['Клиенты', Client::count()],
                ['Заказы', Order::count()],
                ['Ремонты', Repair::count()],
                ['Отзывы', Review::count()],
                ['Бонусы клиентов', ClientBonus::count()],
            ]
        );

        $this->newLine();
        $this->info('🔑 Данные для входа:');
        $this->info('Менеджеры: anna.manager@zatochka-pro.ru / password');
        $this->info('Мастера: mikhail.master@zatochka-pro.ru / password');
        $this->info('Клиенты: любой номер телефона / password');
    }
}
