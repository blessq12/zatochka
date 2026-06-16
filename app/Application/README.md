# Application Layer

Use cases по bounded contexts. CQRS-lite.

## Структура

```
app/Application/{BC}/
├── Command/           # write intent + input DTO
├── CommandHandler/    # orchestration, транзакции
├── Query/             # read intent + input DTO
├── QueryHandler/      # чтение через domain ports
└── Presenter/         # output DTO для API/Filament
```

## BC

OrderFulfillment, ClientPortal, Catalog, Equipment, Warehouse, Identity

## Правила

- Handler → Domain ports (`*RepositoryInterface`), не Eloquent
- Cross-BC — только в CommandHandler/QueryHandler
- Http/Filament вызывает Handler, не Domain
