# oihana/php-models — document-model layer for PHP

![Language](https://img.shields.io/badge/language-English-blue)

`oihana/php-models` is a PHP 8.4+ library providing a **document-model layer**: a base `Model` integrated with a DI container, a composable CRUD document layer (`DocumentsTrait`), PDO-backed models, a declarative per-property **alter** pipeline, PSR-16 collection **caching**, and model lifecycle **signals & notices**.

![Oihana PHP Models](https://raw.githubusercontent.com/BcommeBois/oihana-php-models/main/assets/images/oihana-php-models-logo-inline-512x160.png)

## Who this documentation is for

PHP developers who want to:

- model documents with a single, composable **CRUD** surface — `DocumentsTrait`;
- query relational sources through **PDO** with bound parameters — `PDOModel`, `PDOTrait`;
- declaratively **transform** document properties (cast, hydrate, map, normalize…) — `AlterDocumentTrait` + the `Alter` enum;
- **cache** collections behind a PSR-16 store — `CacheableTrait`;
- react to model **lifecycle events** (before/after insert, update, delete…) — the `Has*Signals` traits + `notices`;
- build **Schema.org-aware** models on `org\schema\Thing` — `SchemaTrait`.

## Quick start

```php
use DI\Container;
use oihana\models\pdo\PDOModel;

$container = new Container();

$model = new PDOModel( $container ,
[
    'pdo'    => 'my_pdo_service', // a PDO instance or a container service id
    'schema' => MyEntity::class,
]);

$record  = $model->fetch( 'SELECT * FROM users WHERE id = :id' , [ 'id' => 123 ] );
$records = $model->fetchAll( 'SELECT * FROM users' );
```

For full details (options, contracts, enums), see the table of contents below.

## Table of contents

### Getting started — [`getting-started/`](getting-started/)

- [Introduction](getting-started/introduction.md) — what the library does and the *oihana* philosophy.
- [Installation](getting-started/installation.md) — PHP 8.4+ requirement and `composer require`.
- [Dependencies](getting-started/dependencies.md) — the runtime packages and their role.

### Usage

- [Models](models.md) — the base `Model`, `ModelTrait`, and the CRUD interfaces.
- [Documents](documents.md) — `DocumentsTrait`: list, get, count, insert, update, delete and more.
- [PDO](pdo.md) — `PDOModel` and `PDOTrait` for relational sources.
- [Alters](alters.md) — the declarative per-property transform pipeline.
- [Cache](cache.md) — `CacheableTrait` and PSR-16 collection caching.
- [Signals & notices](signals-notices.md) — model lifecycle events.
- [Enumerations](enums.md) — `Alter`, `ModelParam`, `NoticeType`.
- [Helpers](helpers.md) — the autoloaded free functions.

### Cross-cutting

- [Tests & coverage](testing.md) — run the PHPUnit suite and measure coverage.

## Source code

The library code lives under [`src/oihana/models/`](../../src/oihana/models/) — namespace `oihana\models`.

## See also

- [Packagist `oihana/php-models`](https://packagist.org/packages/oihana/php-models) — the package page.
- [API reference (phpDocumentor)](https://bcommebois.github.io/oihana-php-models) — class-level generated reference.
