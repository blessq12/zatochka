# ⚠️ Устарело: BC Catalog

BC **Catalog** разделён на:

| Было (Catalog) | Стало |
|----------------|-------|
| Branch, SiteSetting | [Company](../Company/) — `Branch`, `SiteContent` |
| PriceBlock, PriceItem | [Pricing](../Pricing/) |
| `GetPublicBootstrap` | [PublicSite](../PublicSite/) — Application-фасад |

Папки `app/Domain/Catalog/`, `Infrastructure/Catalog/`, `Application/Catalog/` — **legacy**, не подключены в DI и не используются в runtime.

Актуальная документация — см. ссылки выше и [index.md](../../index.md).
