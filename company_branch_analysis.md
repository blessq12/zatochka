# 🏢 Анализ системы компании и филиалов

## 🎯 **Текущее состояние**

### **Что есть:**

✅ **Модели БД** - `Company`, `Branch`  
✅ **Миграции** - структура таблиц создана  
✅ **Filament UI** - ресурсы для управления  
✅ **Связи** - Company ↔ Branch, Branch ↔ Order

### **Что НЕ реализовано:**

❌ **Доменные сущности** - нет в Domain слое  
❌ **Доменные сервисы** - нет бизнес-логики  
❌ **События** - нет доменных событий  
❌ **Application сценарии** - нет API для UI  
❌ **Репозитории** - нет доступа к данным  
❌ **Мапперы** - нет конвертации домен ↔ Eloquent

---

## 🏗️ **Архитектура (текущая)**

### **Слои:**

```
┌─────────────────┐
│   Filament UI   │ ← Готово (CompanyResource, BranchResource)
├─────────────────┤
│   Models        │ ← Готово (Company, Branch)
├─────────────────┤
│   Domain        │ ← ОТСУТСТВУЕТ!
├─────────────────┤
│ Infrastructure  │ ← ОТСУТСТВУЕТ!
└─────────────────┘
```

---

## 📊 **Структура БД (готова)**

### **Company:**

-   `id`, `name`, `legal_name`
-   `inn` (unique), `kpp`, `ogrn`
-   `legal_address`, `website`
-   `bank_name`, `bank_bik`, `bank_account`, `bank_cor_account`
-   `logo_path`, `additional_data` (json)
-   `is_deleted`, `timestamps`

### **Branch:**

-   `id`, `company_id` (FK → Company)
-   `name`, `code` (unique)
-   `address`, `phone`, `email`
-   `working_hours`, `latitude`, `longitude`
-   `description`, `additional_data` (json)
-   `is_active`, `is_deleted`, `timestamps`

---

## 🎨 **UI (готово)**

### **CompanyResource (Manager):**

-   Создание/редактирование компаний
-   Юридические реквизиты
-   Банковская информация
-   Счётчик филиалов
-   Ссылка на филиалы

### **BranchResource (Manager):**

-   Создание/редактирование филиалов
-   Привязка к компании
-   Контактная информация
-   Геолокация (координаты)
-   Счётчик заказов
-   Ссылка на заказы филиала

---

## 🔗 **Связи и интеграции**

### **Company ↔ Branch:**

```php
// Company имеет много филиалов
public function branches()
{
    return $this->hasMany(Branch::class);
}

// Branch принадлежит компании
public function company()
{
    return $this->belongsTo(Company::class);
}
```

### **Branch ↔ Order:**

```php
// Branch имеет много заказов
public function orders()
{
    return $this->hasMany(Order::class);
}

// Order принадлежит филиалу
public function branch()
{
    return $this->belongsTo(Branch::class);
}
```

### **Branch ↔ Repair:**

```php
// Branch имеет много ремонтов
public function repairs()
{
    return $this->hasMany(Repair::class);
}
```

---

## 🚧 **Что нужно реализовать**

### **1. Доменные сущности:**

```php
// Company (агрегат)
class Company extends AggregateRoot
{
    private CompanyId $id;
    private CompanyName $name;
    private LegalName $legalName;
    private INN $inn;
    private ?KPP $kpp;
    private ?OGRN $ogrn;
    private Address $legalAddress;
    private ?Website $website;
    private BankDetails $bankDetails;
    private ?LogoPath $logoPath;
    private AdditionalData $additionalData;

    public function addBranch(Branch $branch): void
    public function updateLegalInfo(LegalInfo $info): void
    public function updateBankDetails(BankDetails $details): void
}

// Branch (сущность)
class Branch
{
    private BranchId $id;
    private CompanyId $companyId;
    private BranchName $name;
    private BranchCode $code;
    private Address $address;
    private Phone $phone;
    private Email $email;
    private ?WorkingHours $workingHours;
    private ?Coordinates $coordinates;
    private ?Description $description;
    private AdditionalData $additionalData;
    private bool $isActive;

    public function activate(): void
    public function deactivate(): void
    public function updateLocation(Coordinates $coordinates): void
}
```

### **2. Value Objects:**

```php
// Company
class CompanyName { private string $value; }
class LegalName { private string $value; }
class INN { private string $value; } // 12 цифр
class KPP { private string $value; } // 9 цифр
class OGRN { private string $value; } // 15 цифр
class Address { private string $value; }
class Website { private string $value; }

// Bank
class BankName { private string $value; }
class BIK { private string $value; } // 9 цифр
class BankAccount { private string $value; } // 20 цифр
class BankDetails { private BankName $name, BIK $bik, BankAccount $account, BankAccount $corAccount; }

// Branch
class BranchCode { private string $value; } // уникальный код
class WorkingHours { private string $value; }
class Coordinates { private float $latitude, float $longitude; }
class Description { private string $value; }
```

### **3. Доменные сервисы:**

```php
class CompanyService
{
    public function createCompany(CreateCompanyRequest $request): Company
    public function addBranch(CompanyId $companyId, CreateBranchRequest $request): Branch
    public function updateLegalInfo(CompanyId $companyId, LegalInfo $info): void
    public function updateBankDetails(CompanyId $companyId, BankDetails $details): void
}

class BranchService
{
    public function createBranch(CreateBranchRequest $request): Branch
    public function activateBranch(BranchId $branchId): void
    public function deactivateBranch(BranchId $branchId): void
    public function updateLocation(BranchId $branchId, Coordinates $coordinates): void
}
```

### **4. События:**

```php
class CompanyCreated extends DomainEvent
{
    public function __construct(
        public readonly CompanyId $companyId,
        public readonly CompanyName $name,
        public readonly INN $inn
    ) {}
}

class BranchAdded extends DomainEvent
{
    public function __construct(
        public readonly CompanyId $companyId,
        public readonly BranchId $branchId,
        public readonly BranchName $branchName
    ) {}
}

class BranchActivated extends DomainEvent
{
    public function __construct(
        public readonly BranchId $branchId,
        public readonly CompanyId $companyId
    ) {}
}
```

### **5. Application сценарии:**

```php
class CreateCompany
{
    public function execute(CreateCompanyRequest $request): Result
    {
        // Валидация → Создание компании → События
    }
}

class AddBranch
{
    public function execute(AddBranchRequest $request): Result
    {
        // Валидация → Создание филиала → События
    }
}

class UpdateCompanyInfo
{
    public function execute(UpdateCompanyInfoRequest $request): Result
    {
        // Валидация → Обновление → События
    }
}
```

---

## 🎯 **Бизнес-правила и инварианты**

### **Company:**

-   ИНН должен быть уникальным в системе
-   Юридическое название обязательно
-   Юридический адрес обязателен
-   Банковские реквизиты могут быть неполными

### **Branch:**

-   Код филиала должен быть уникальным в рамках компании
-   Адрес обязателен
-   Телефон или email обязателен
-   Координаты опциональны, но если есть - валидные

### **Связи:**

-   Филиал не может существовать без компании
-   При удалении компании удаляются все филиалы (cascade)
-   Заказы привязаны к конкретному филиалу

---

## 🚀 **Приоритеты реализации**

### **Высокий:**

1. **Доменные сущности** - основа бизнес-логики
2. **Value Objects** - валидация и типизация
3. **Доменные сервисы** - операции с компаниями/филиалами

### **Средний:**

4. **События** - интеграция с системой уведомлений
5. **Application сценарии** - API для UI
6. **Репозитории** - доступ к данным

### **Низкий:**

7. **Мапперы** - конвертация домен ↔ Eloquent
8. **Расширенная логика** - мультитенантность, права доступа

---

## 💡 **Рекомендации**

### **1. Начать с домена:**

-   Реализовать `Company` как агрегат
-   Создать `Branch` как сущность
-   Добавить бизнес-правила и валидацию

### **2. Value Objects:**

-   Строгая типизация для ИНН, КПП, ОГРН
-   Валидация координат
-   Уникальность кодов филиалов

### **3. Интеграция:**

-   События при создании/изменении
-   Уведомления о новых филиалах
-   Аудит изменений

---

## 🎯 **Итог**

**Система компании и филиалов имеет готовую инфраструктуру (БД + UI), но требует реализации доменной логики.**

-   ✅ **UI готов** - можно управлять компаниями и филиалами
-   ✅ **БД готова** - структура и связи созданы
-   ❌ **Логика пуста** - нужно программировать домен

**Следующий шаг:** Реализация доменных сущностей `Company` и `Branch` с бизнес-правилами и валидацией.
