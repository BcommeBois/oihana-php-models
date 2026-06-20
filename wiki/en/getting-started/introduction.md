# Introduction

`oihana/php-models` gathers the document-model building blocks that used to live inside `oihana/php-system`, extracted into a focused package so that a project can depend on the model layer **without** pulling an HTTP stack, a templating engine or a routing layer.

It provides a small, composable surface: a base `Model` wired to a DI container, a single CRUD document layer, PDO-backed models, a declarative property-transform pipeline, PSR-16 caching and lifecycle signals.

## What it provides

| Component | Type | Role |
|---|---|---|
| `Model` | class | Base model integrating a PDO instance with DI-container support. |
| `pdo\PDOModel` | class | PDO-backed model for relational sources. |
| `pdo\PDOTrait` | trait | PDO operations (binding, fetching) for any class. |
| `traits\DocumentsTrait` | trait | The document CRUD layer (list, get, count, insert, update, upsert, replace, delete, truncate, exist, last, stream). |
| `traits\AlterDocumentTrait` | trait | Declarative per-property transform pipeline (driven by the `Alter` enum). |
| `traits\CacheableTrait` | trait | PSR-16 collection caching. |
| `traits\signals\Has*Signals` | traits | Model lifecycle signals (before/after each CRUD verb). |
| `notices\Before*` / `notices\After*` | classes | Notice payloads carried by the lifecycle signals. |
| `traits\SchemaTrait` | trait | Schema.org awareness on top of `org\schema\Thing`. |
| `interfaces\*` | interfaces | Per-verb CRUD contracts + the aggregating `DocumentsModel`. |
| `enums\Alter` / `ModelParam` / `NoticeType` | classes | Strongly-typed configuration keys (no *magic strings*). |

## The *oihana* philosophy

- **PHP 8.4+ only** — typed constants, property hooks, no legacy shims.
- **No *magic strings*** — every configuration key is a typed constant in a `ConstantsTrait`-based class (`Alter`, `ModelParam`, `NoticeType`); the project never uses native PHP enums.
- **Composable** — each trait has a single responsibility and can be combined freely.
- **Tested** — 100% line coverage, strict PHPUnit mode (see [Tests & coverage](../testing.md)).

## Next steps

- [Installation](installation.md)
- [Dependencies](dependencies.md)
- [Models](../models.md)
