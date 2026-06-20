# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-06-20

First release. The `oihana\models` namespace is extracted from
`oihana/php-system` into its own focused document-model package for PHP 8.4+.

### Added
- Project scaffolding: `composer.json`, `phpunit.xml`, `phpdoc.xml`,
  CI and Docs GitHub workflows, coverage tooling, phpDocumentor template,
  README, CONTRIBUTING and license.
- Brand assets (logos) under `assets/images/`.
- The `oihana\models` library, imported unchanged from `oihana/php-system`
  (identical FQNs):
  - `Model` — base model integrating a PDO instance with DI-container support.
  - `pdo\PDOModel` / `pdo\PDOTrait` — PDO-backed models for relational sources.
  - `traits\DocumentsTrait` — the document CRUD layer (list, get, count,
    insert, update, upsert, replace, delete, truncate, exist, last, stream),
    with `ConditionsTrait`, `ListModelTrait`, `EnsureKeysTrait`, `BindsTrait`.
  - `traits\AlterDocumentTrait` + `traits\alters\*` — the declarative
    per-property transform pipeline driven by the `Alter` enum.
  - `traits\CacheableTrait` — PSR-16 collection caching (Scrapbook backend).
  - `traits\signals\Has*Signals` + `notices\Before*` / `notices\After*` —
    model lifecycle events built on `oihana/php-signals`.
  - `traits\SchemaTrait`, `traits\ModelTrait`, `traits\PropertyTrait`,
    `traits\ThrowableTrait` — supporting traits.
  - `interfaces\*` — per-verb CRUD contracts and the aggregating
    `DocumentsModel` interface.
  - `enums\Alter`, `enums\ModelParam`, `enums\NoticeType` —
    `ConstantsTrait`-based typed-constant classes (no native enums).
  - Free functions `helpers\{getModel, getDocumentsModel, documentUrl,
    cacheCollection}`, wired via composer `autoload.files`.
- Unit-test suite imported from `oihana/php-system` (PHPUnit, strict mode),
  plus the `tests\oihana\traits\mocks\MockPDOClass` fixture.
  **100% line coverage** (615/615 lines, 90/90 methods, 50/50 classes),
  356 tests.
- Bilingual user guide under `wiki/` (English + French): getting started
  (introduction, installation, dependencies), models, documents, PDO, alters,
  cache, signals & notices, enumerations, helpers and a testing guide.

### Fixed
- `NoticeType::AFTER_UPDATE` resolved to `'afterReplace'` (a copy-paste
  duplicate of `AFTER_REPLACE`), making `AfterUpdate` notices indistinguishable
  from `AfterReplace` by their type. Set it to `'afterUpdate'`. Added
  `NoticeTypeTest` freezing every constant's literal value and their uniqueness.

### Changed
- Enriched the `NoticeType` class/constant PHPDoc and tidied docblocks,
  `@package` tags and code formatting across the models source.
