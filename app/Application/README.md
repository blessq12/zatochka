# Application Layer

Use cases (commands/queries) и orchestration cross-BC.

## Структура (целевая)

```
app/Application/
├── OrderFulfillment/
│   ├── Commands/
│   └── Handlers/
├── ClientPortal/
├── Catalog/
├── Equipment/
├── Warehouse/
└── Identity/
```

## Правила

- Handler зависит от **портов** Domain (`*RepositoryInterface`), не от Eloquent
- Cross-BC сценарии — только здесь (CreateOrder из SiteLead, SubmitReview)
- Presentation (Http/Filament) вызывает Handler, не Domain напрямую
