# 🔄 Система доменных событий: Пошаговое объяснение

## 📋 Содержание

1. [Общая архитектура системы](#общая-архитектура-системы)
2. [Компоненты системы](#компоненты-системы)
3. [Жизненный цикл события](#жизненный-цикл-события)
4. [Хранение подписчиков](#хранение-подписчиков)
5. [Процесс подписки](#процесс-подписки)
6. [Процесс публикации](#процесс-публикации)
7. [Примеры работы](#примеры-работы)
8. [Преимущества системы](#преимущества-системы)

---

## 🏗️ Общая архитектура системы

### **Схема работы:**

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Доменный      │    │   EventBus      │    │   Слушатели     │
│   сервис        │───▶│   (Шина         │───▶│   (Subscribers) │
│                 │    │   событий)      │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Создаёт       │    │   Хранит        │    │   Выполняют     │
│   событие       │    │   подписчиков   │    │   действия      │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### **Принцип работы:**

1. **Доменный сервис** создаёт и публикует событие
2. **EventBus** получает событие и уведомляет всех подписчиков
3. **Слушатели** выполняют свои действия в ответ на событие

---

## 🧩 Компоненты системы

### **1. EventBusInterface (Интерфейс)**

```php
interface EventBusInterface
{
    public function publish(object $event): void;           // Публикация события
    public function subscribe(string $eventClass, callable $handler): void;  // Подписка
    public function unsubscribe(string $eventClass, callable $handler): void; // Отписка
    public function hasSubscribers(string $eventClass): bool;                 // Проверка
    public function getSubscribers(string $eventClass): array;                // Получение списка
}
```

**Назначение:** Определяет контракт для шины событий

### **2. EventBus (Реализация)**

```php
class EventBus implements EventBusInterface
{
    private array $subscribers = [];  // ← ВОТ ГДЕ ХРАНЯТСЯ ПОДПИСЧИКИ!

    // Методы реализации интерфейса
}
```

**Назначение:** Реальная реализация шины событий

### **3. DomainEvent (Базовый класс)**

```php
abstract class DomainEvent
{
    public readonly DateTimeImmutable $occurredOn;  // Когда произошло
    public readonly string $eventId;                // Уникальный ID события

    abstract public function eventName(): string;   // Имя события
    abstract public function eventData(): array;    // Данные события
}
```

**Назначение:** Базовый класс для всех доменных событий

### **4. Конкретные события (например, UserRegistered)**

```php
class UserRegistered extends DomainEvent
{
    public function __construct(
        public readonly UserId $userId,
        public readonly string $name,
        public readonly Email $email
    ) {
        parent::__construct();  // Вызывает конструктор базового класса
    }
}
```

**Назначение:** Конкретные типы событий с данными

### **5. Слушатели (Subscribers)**

```php
class UserRegisteredSubscriber
{
    public function handle(UserRegistered $event): void
    {
        // Логика обработки события
        Log::info('User registered', [...]);
        $this->sendWelcomeEmail($event);
    }
}
```

**Назначение:** Обработчики событий

---

## 🔄 Жизненный цикл события

### **Пошаговый процесс:**

```
1. Создание события
   ↓
2. Публикация в EventBus
   ↓
3. EventBus находит всех подписчиков
   ↓
4. Выполнение каждого слушателя
   ↓
5. Логирование результатов
   ↓
6. Завершение обработки
```

---

## 💾 Хранение подписчиков

### **Где хранятся подписчики?**

```php
class EventBus implements EventBusInterface
{
    private array $subscribers = [];  // ← ВОТ ОНО!
}
```

### **Структура хранения:**

```php
$subscribers = [
    // Класс события → массив обработчиков
    'App\Domain\Users\Events\UserRegistered' => [
        // Обработчик 1: метод класса
        [UserRegisteredSubscriber, 'handle'],

        // Обработчик 2: анонимная функция
        function(UserRegistered $event) { ... },

        // Обработчик 3: другой метод
        [AnotherSubscriber, 'processUserRegistration']
    ],

    'App\Domain\Users\Events\UserActivated' => [
        [UserActivatedSubscriber, 'handle'],
        [NotificationSubscriber, 'sendActivationEmail']
    ]
];
```

### **Типы обработчиков:**

1. **Методы классов:** `[ClassName, 'methodName']`
2. **Анонимные функции:** `function($event) { ... }`
3. **Замыкания:** `fn($event) => ...`

---

## 📝 Процесс подписки

### **Как происходит подписка:**

#### **1. В провайдере (EventsDomainServiceProvider):**

```php
public function boot(): void
{
    $this->registerDomainEventListeners();
}

private function registerDomainEventListeners(): void
{
    $eventBus = $this->app->make(EventBusInterface::class);
    $userRegisteredSubscriber = $this->app->make(UserRegisteredSubscriber::class);

    // ПОДПИСКА: говорим EventBus'у слушать событие UserRegistered
    $eventBus->subscribe(
        UserRegistered::class,                    // ← Тип события
        [$userRegisteredSubscriber, 'handle']     // ← Обработчик
    );
}
```

#### **2. В EventBus::subscribe():**

```php
public function subscribe(string $eventClass, callable $handler): void
{
    // 1. Проверяем, есть ли уже массив для этого типа события
    if (!isset($this->subscribers[$eventClass])) {
        $this->subscribers[$eventClass] = [];  // Создаём пустой массив
    }

    // 2. Добавляем обработчик в массив
    $this->subscribers[$eventClass][] = $handler;

    // 3. Логируем подписку
    Log::debug('Event handler subscribed', [
        'event' => $eventClass,
        'handler' => is_array($handler) ? get_class($handler[0]) . '::' . $handler[1] : 'closure',
        'total_subscribers' => count($this->subscribers[$eventClass])
    ]);
}
```

#### **3. Результат подписки:**

```php
// До подписки:
$subscribers = [];

// После подписки:
$subscribers = [
    'App\Domain\Users\Events\UserRegistered' => [
        [UserRegisteredSubscriber, 'handle']
    ]
];
```

---

## 📢 Процесс публикации

### **Как происходит публикация события:**

#### **1. Создание события в доменном сервисе:**

```php
// В UserDomainService::register()
$user = User::register($userId, $name, $emailVO, $passwordHash);

// ... сохранение пользователя ...

// ПУБЛИКАЦИЯ СОБЫТИЯ
$this->events->publish(new UserRegistered($user->userId(), $user->name(), $user->email()));
```

#### **2. Вызов EventBus::publish():**

```php
public function publish(object $event): void
{
    try {
        // 1. Получаем класс события
        $eventClass = get_class($event);

        // 2. Логируем начало публикации
        Log::debug('Publishing domain event', [
            'event' => $eventClass,
            'timestamp' => now()->toISOString()
        ]);

        // 3. Ищем подписчиков на этот тип события
        if (isset($this->subscribers[$eventClass])) {
            // 4. Выполняем каждого подписчика
            foreach ($this->subscribers[$eventClass] as $handler) {
                try {
                    $handler($event);  // ← ВЫЗЫВАЕМ ОБРАБОТЧИК!

                    Log::debug('Event handler executed successfully', [...]);
                } catch (\Exception $e) {
                    Log::error('Event handler failed', [...]);
                    continue;  // Продолжаем с другими обработчиками
                }
            }
        }

        // 5. Логируем успешное завершение
        Log::debug('Event published successfully', [
            'event' => $eventClass,
            'subscribers_count' => isset($this->subscribers[$eventClass]) ? count($this->subscribers[$eventClass]) : 0
        ]);
    } catch (\Exception $e) {
        Log::error('Failed to publish event', [...]);
        throw $e;
    }
}
```

#### **3. Выполнение обработчиков:**

```php
// EventBus находит подписчиков:
$handlers = $this->subscribers['App\Domain\Users\Events\UserRegistered'];

// И выполняет каждого:
foreach ($handlers as $handler) {
    $handler($event);  // Вызывает UserRegisteredSubscriber::handle($event)
}
```

---

## 💡 Примеры работы

### **Пример 1: Полный цикл создания пользователя**

#### **Шаг 1: Создание события**

```php
// В UserDomainService
$event = new UserRegistered($userId, $name, $email);
// Событие создано с:
// - eventId = "event_64f8a1b2c3d4e5f6"
// - occurredOn = "2024-01-15T10:30:00+00:00"
```

#### **Шаг 2: Публикация**

```php
$this->eventBus->publish($event);
// EventBus получает событие типа UserRegistered
```

#### **Шаг 3: Поиск подписчиков**

```php
// EventBus ищет в $subscribers:
$eventClass = 'App\Domain\Users\Events\UserRegistered';
$handlers = $this->subscribers[$eventClass] ?? [];
// Находит: [UserRegisteredSubscriber, 'handle']
```

#### **Шаг 4: Выполнение обработчиков**

```php
// Вызывает UserRegisteredSubscriber::handle($event)
foreach ($handlers as $handler) {
    $handler($event);
}
```

#### **Шаг 5: Результат**

-   Пользователь зарегистрирован
-   Отправлено приветственное письмо
-   Записано в лог
-   Событие обработано

### **Пример 2: Добавление нового слушателя**

#### **Добавляем слушатель для отправки уведомления администратору:**

```php
// В EventsDomainServiceProvider
$adminNotifier = $this->app->make(AdminNotifierSubscriber::class);

$eventBus->subscribe(
    UserRegistered::class,
    [$adminNotifier, 'notifyAdmin']
);

// Теперь $subscribers выглядит так:
$subscribers = [
    'App\Domain\Users\Events\UserRegistered' => [
        [UserRegisteredSubscriber, 'handle'],        // Отправка приветствия
        [AdminNotifierSubscriber, 'notifyAdmin']     // Уведомление админа
    ]
];
```

#### **При публикации события UserRegistered:**

1. Выполнится `UserRegisteredSubscriber::handle()`
2. Выполнится `AdminNotifierSubscriber::notifyAdmin()`
3. Оба обработчика получат одно и то же событие

---

## 🎯 Преимущества системы

### **1. Слабая связанность**

```php
// UserDomainService НЕ знает о слушателях
$this->events->publish(new UserRegistered(...));

// EventBus НЕ знает о конкретных обработчиках
$handler($event);  // Просто вызывает функцию
```

### **2. Расширяемость**

```php
// Добавить новый обработчик - просто подписаться
$eventBus->subscribe(UserRegistered::class, [NewSubscriber, 'handle']);

// Ничего не нужно менять в существующем коде!
```

### **3. Тестируемость**

```php
// В тестах можно легко мокать EventBus
$mockEventBus = Mockery::mock(EventBusInterface::class);
$mockEventBus->shouldReceive('publish')->once();

// Или создавать реальный EventBus для интеграционных тестов
$eventBus = new EventBus();
$eventBus->subscribe(UserRegistered::class, $testHandler);
```

### **4. Независимость от фреймворка**

```php
// EventBus работает без Laravel
$eventBus = new EventBus();
$eventBus->subscribe(UserRegistered::class, $handler);
$eventBus->publish($event);

// Можно использовать в CLI, API, веб-приложении
```

---

## 🔍 Отладка и мониторинг

### **1. Логирование**

```php
// EventBus автоматически логирует:
Log::debug('Publishing domain event', [...]);
Log::debug('Event handler executed successfully', [...]);
Log::debug('Event published successfully', [...]);
```

### **2. Статистика**

```php
// Можно получить информацию о подписчиках
$hasSubscribers = $eventBus->hasSubscribers(UserRegistered::class);
$subscribers = $eventBus->getSubscribers(UserRegistered::class);
$stats = $eventBus->getEventStats();
```

### **3. Обработка ошибок**

```php
// Если один обработчик упал, остальные продолжают работать
try {
    $handler($event);
} catch (\Exception $e) {
    Log::error('Event handler failed', [...]);
    continue;  // Продолжаем с другими
}
```

---

## 🚀 Расширение системы

### **1. Добавление новых типов событий**

```php
// Создаём новое событие
class UserActivated extends DomainEvent
{
    public function __construct(public readonly UserId $userId)
    {
        parent::__construct();
    }

    public function eventName(): string { return 'UserActivated'; }
    public function eventData(): array { return ['user_id' => (string) $this->userId]; }
}

// Создаём слушатель
class UserActivatedSubscriber
{
    public function handle(UserActivated $event): void
    {
        // Логика активации
    }
}

// Подписываемся в провайдере
$eventBus->subscribe(UserActivated::class, [UserActivatedSubscriber::class, 'handle']);
```

### **2. Асинхронная обработка**

```php
// Можно добавить очередь для тяжёлых операций
class AsyncEventBus implements EventBusInterface
{
    public function publish(object $event): void
    {
        // Вместо синхронного вызова
        // dispatch(new ProcessEventJob($event));
    }
}
```

### **3. Персистентность событий**

```php
// Сохранение событий в базу для аудита
class PersistentEventBus implements EventBusInterface
{
    public function publish(object $event): void
    {
        // Сохраняем событие
        $this->eventRepository->save($event);

        // Уведомляем подписчиков
        $this->notifySubscribers($event);
    }
}
```

---

## 📚 Ключевые принципы

### **1. "Tell, Don't Ask"**

-   Домен **говорит** о том, что произошло
-   EventBus **решает**, кто должен об этом узнать
-   Слушатели **действуют** на основе события

### **2. Слабая связанность**

-   Издатель события не знает о слушателях
-   Слушатели не знают друг о друге
-   EventBus координирует взаимодействие

### **3. Единственная ответственность**

-   EventBus отвечает только за события
-   События содержат только данные
-   Слушатели выполняют только свои задачи

---

_Эта система даёт вам мощный, но простой способ организации взаимодействия между компонентами домена без создания жёстких зависимостей._ 🎉
