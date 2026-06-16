# Application Layer

Use cases по bounded contexts. **Сейчас — только каркас папок.**

## Статус

| BC | Command | CommandHandler | Query | QueryHandler | Presenter |
|----|---------|----------------|-------|--------------|-----------|
| Все 6 BC | `.gitkeep` | `.gitkeep` | `.gitkeep` | `.gitkeep` | `.gitkeep` |

Классов PHP нет. Бизнес-сценарии из `es/04-команды` ещё не реализованы.

## Структура (факт)

```
app/Application/{BC}/
├── Command/
├── CommandHandler/
├── Query/
├── QueryHandler/
└── Presenter/
```

BC: `OrderFulfillment`, `ClientPortal`, `Catalog`, `Equipment`, `Warehouse`, `Identity`

## Правила (на реализацию)

- Handler → `Domain/{BC}/Repository/*Interface`, не Eloquent
- Cross-BC — только в CommandHandler (inject ports соседних BC)
- Http/Filament вызывает Handler, не Domain напрямую

## DI

Repository bindings: `app/Infrastructure/Shared/Provider/PersistenceServiceProvider.php`
