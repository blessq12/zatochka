# Система уведомлений (Toast)

## Установка и настройка

Система тостов уже настроена и готова к использованию. Используется библиотека `vue-toastification@next`.

## Использование

### Глобальный сервис

Сервис доступен глобально через `window.toastService`:

```javascript
// Базовые методы
window.toastService.success("Успешно!");
window.toastService.error("Ошибка!");
window.toastService.warning("Предупреждение!");
window.toastService.info("Информация!");

// Загрузка
const toastId = window.toastService.loading("Загружаем...");
// Обновить тост
window.toastService.update(toastId, {
    type: "success",
    content: "Готово!",
});

// Закрыть тост
window.toastService.dismiss(toastId);

// Закрыть все тосты
window.toastService.clear();
```

### Кастомные методы

```javascript
// Готовые шаблоны
window.toastService.created(); // "Создано успешно!"
window.toastService.updated(); // "Обновлено успешно!"
window.toastService.saved(); // "Сохранено успешно!"
window.toastService.deleted(); // "Удалено успешно!"

// Ошибки
window.toastService.validationError("Текст ошибки"); // Ошибка валидации
window.toastService.networkError(); // "Ошибка сети. Проверьте соединение."
window.toastService.serverError(); // "Ошибка сервера. Попробуйте позже."
```

### В компонентах Vue

```javascript
export default {
    methods: {
        showSuccess() {
            if (window.toastService) {
                window.toastService.success("Операция выполнена успешно!");
            }
        },

        showError() {
            if (window.toastService) {
                window.toastService.error("Произошла ошибка!");
            }
        },

        handleFormSubmit() {
            try {
                // Логика отправки формы
                window.toastService.success("Форма отправлена!");
            } catch (error) {
                window.toastService.error("Ошибка при отправке формы");
            }
        },
    },
};
```

## Конфигурация

Настройки тостов находятся в `resources/js/app.js`:

```javascript
const toastOptions = {
    position: "top-right", // Позиция
    timeout: 5000, // Время показа (мс)
    closeOnClick: true, // Закрыть по клику
    pauseOnFocusLoss: true, // Пауза при потере фокуса
    pauseOnHover: true, // Пауза при наведении
    draggable: true, // Возможность перетаскивания
    draggablePercent: 0.6, // Процент для перетаскивания
    showCloseButtonOnHover: false, // Показывать кнопку закрытия при наведении
    hideProgressBar: false, // Скрыть прогресс бар
    closeButton: "button", // Тип кнопки закрытия
    icon: true, // Показывать иконки
    rtl: false, // Справа налево
    transition: "Vue-Toastification__bounce", // Анимация
    maxToasts: 20, // Максимум тостов
    newestOnTop: true, // Новые сверху
    filterBeforeCreate: (toast, toasts) => {
        // Фильтр дубликатов
        if (toasts.filter((t) => t.type === toast.type).length !== 0) {
            return false;
        }
        return toast;
    },
    toastClassName: "custom-toast", // CSS класс тоста
    bodyClassName: "custom-toast-body", // CSS класс тела
    containerClassName: "custom-toast-container", // CSS класс контейнера
};
```

## Стили

Кастомные стили находятся в `resources/css/app.css`:

-   `.custom-toast` - основной стиль тоста
-   `.custom-toast-body` - стиль тела тоста
-   `.custom-toast-container` - стиль контейнера
-   Поддержка темной темы
-   Анимации появления/исчезновения

## Типы уведомлений

1. **success** - зеленый, для успешных операций
2. **error** - красный, для ошибок
3. **warning** - желтый, для предупреждений
4. **info** - синий, для информации
5. **loading** - фиолетовый, для загрузки

## Примеры использования

### Форма заказа

```javascript
// Успешное создание заказа
window.toastService.success(
    "Заказ успешно создан! Мы свяжемся с вами в ближайшее время."
);

// Ошибка валидации
window.toastService.validationError("Проверьте правильность заполнения формы");

// Ошибка сети
window.toastService.networkError();
```

### Отзывы

```javascript
// Успешная отправка отзыва
window.toastService.success("Отзыв отправлен на модерацию!");

// Ошибка отправки
window.toastService.error("Ошибка при отправке отзыва");
```

### Регистрация

```javascript
// Успешная регистрация
window.toastService.success("Аккаунт создан успешно!");
```

## Fallback

Если сервис тостов недоступен, используется стандартный `alert()`:

```javascript
if (window.toastService) {
    window.toastService.success("Сообщение");
} else {
    alert("Сообщение");
}
```
