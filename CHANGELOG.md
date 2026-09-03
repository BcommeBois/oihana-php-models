# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- `AlterDocumentTrait::alter()` gains an optional third argument `array $context = []`:
  an opaque context threaded through the whole alteration chain — including the recursive
  pass over a list of documents — and forwarded to every `Alter::MAP` callback as its
  **6th argument** (`function ( $document , $container , $key , $value , $params , $context )`).
  It lets a caller hand request-scoped information (a skin, a locale, the originating
  request payload…) to `MAP` callbacks so they can pick their mapping strategy, with no
  mutable shared state.

### Fixed
- `AlterDocumentTrait::alter()` no longer alters a property the document does not carry.
  The guard asked `hasKeyValue()`, which on an object falls back on `property_exists()` —
  true of every property the **class** declares, initialized or not. A typed property no
  source ever filled was therefore altered like any other, and whatever the chain computed
  from nothing was written back onto the document : `Alter::ARRAY` over an uninitialized
  property yields `[]`, and an empty array is an assertion — it reads « we looked, there is
  none » where the truth is « nobody supplied this ». A field absent from a projection came
  back **stated**. The guard now asks `carriesKeyValue()` (`oihana/php-core` 1.2.0), which
  answers whether the document *holds* a value.
- 🔑 **Initialized, never non-null** : a property holding `null` is still altered, as before —
  declaring `= null` is commonplace and reading `null` as absence would have silenced every
  one of those at once. Arrays are unaffected : `array_key_exists()` already asked the right
  question, and a key holding `null` has always been altered.

### Notes
- Fully backward compatible: the parameter defaults to `[]`, and a `MAP` callback that does
  not declare the trailing `$context` keeps working unchanged (PHP discards the surplus
  argument). The context is threaded by value, never stored on the instance, so it is
  reentrant and needs no cleanup.
- `Alter::CALL` intentionally does **not** receive the context: its callables are frequently
  native PHP functions (`strtoupper`, `trim`, …) that reject extra arguments. A `CALL` that
  needs the context should be promoted to `Alter::MAP`.

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
