# Oihana PHP - Models

![Oihana PHP Models](https://raw.githubusercontent.com/BcommeBois/oihana-php-models/main/assets/images/oihana-php-models-logo-inline-512x160.png)

A document model layer for PHP 8.4+: schema-aware models with composable CRUD, PDO and PSR-16 cache integration.

[![Latest Version](https://img.shields.io/packagist/v/oihana/php-models.svg?style=flat-square)](https://packagist.org/packages/oihana/php-models)  
[![Total Downloads](https://img.shields.io/packagist/dt/oihana/php-models.svg?style=flat-square)](https://packagist.org/packages/oihana/php-models)  
[![License](https://img.shields.io/packagist/l/oihana/php-models.svg?style=flat-square)](LICENSE)

## 📚 Documentation

User guides (FR + EN), with narrative explanations and examples:

| 🇬🇧 **[English documentation](wiki/en/README.md)** | 🇫🇷 **[Documentation française](wiki/fr/README.md)** |
|---|---|
| Getting started, models, CRUD traits, PDO, cache, signals. | Démarrage, modèles, traits CRUD, PDO, cache, signaux. |

Auto-generated API reference (phpDocumentor):  
👉 https://bcommebois.github.io/oihana-php-models

## 🚀 Features

- 📄 Document models with composable CRUD traits (list, get, count, insert, update, delete).
- 🗄️ PDO-backed models for relational sources.
- 🧬 Schema.org-aware models built on `org\schema\Thing`.
- ⚡ PSR-16 cache integration (Scrapbook) for collection caching.
- 📡 Signals & notices for model lifecycle events.
- 🧪 Full unit-test coverage ensuring reliability and maintainability.

💡 Designed to be lightweight, testable, and compatible with any PHP 8.4+ project.

## 📦 Installation

> **Requires [PHP 8.4+](https://php.net/releases/)**  

Install via [Composer](https://getcomposer.org):
```bash
composer require oihana/php-models
```

## ✅ Tests & coverage

Run the full unit-test suite (PHPUnit, strict mode):
```bash
composer test
```

Run a single test case:
```bash
./vendor/bin/phpunit --filter DocumentsTraitTest
```

Measure coverage (requires Xdebug or PCOV):
```bash
composer coverage        # text + Clover + HTML under build/coverage/
composer coverage:md     # readable Markdown summary (build/coverage/COVERAGE.md)
```

The suite runs in **strict mode** and targets **100% line coverage**.

## 🧾 License

This project is licensed under the [Mozilla Public License 2.0 (MPL-2.0)](https://www.mozilla.org/en-US/MPL/2.0/).

## 👤 About the author

* Author : Marc ALCARAZ (aka eKameleon)
* Mail : marc@ooop.fr
* Website : http://www.ooop.fr

## 🛠️ Generate the Documentation

We use [phpDocumentor](https://phpdoc.org/) to generate the documentation into the ./docs folder.

### Usage
Run the command : 
```bash
composer doc
```

## 🔗 Related packages

- [oihana/php-core](https://github.com/BcommeBois/oihana-php-core) – core helpers and utilities used by this library.
- [oihana/php-traits](https://github.com/BcommeBois/oihana-php-traits) – reusable, composable object traits.
- [oihana/php-logging](https://github.com/BcommeBois/oihana-php-logging) – PSR-3 logging building blocks.
- [oihana/php-schema](https://github.com/BcommeBois/oihana-php-schema) – Schema.org value objects and resolvers.
- [oihana/php-signals](https://github.com/BcommeBois/oihana-php-signals) – lightweight signal/slot events.
