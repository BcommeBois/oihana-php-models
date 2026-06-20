# Dependencies

`oihana/php-models` keeps a focused runtime footprint. Here is what it requires
and **why**.

## Runtime dependencies

| Package | Role |
|---|---|
| [`oihana/php-core`](https://github.com/BcommeBois/oihana-php-core) | Core helpers and utilities (array, string, container resolution). |
| [`oihana/php-traits`](https://github.com/BcommeBois/oihana-php-traits) | Reusable object traits (`ConfigTrait`, `ContainerTrait`, `KeyValueTrait`, …) the models build on. |
| [`oihana/php-enums`](https://github.com/BcommeBois/oihana-php-enums) | Typed constant enumerations (`Char`, `Order`, …). |
| [`oihana/php-reflect`](https://github.com/BcommeBois/oihana-php-reflect) | `ConstantsTrait` and hydration/reflection utilities. |
| [`oihana/php-exceptions`](https://github.com/BcommeBois/oihana-php-exceptions) | Shared exception types raised by the models. |
| [`oihana/php-files`](https://github.com/BcommeBois/oihana-php-files) | File helpers used by document/URL helpers. |
| [`oihana/php-logging`](https://github.com/BcommeBois/oihana-php-logging) | PSR-3 logging (`Logger`, `DebugTrait`) used by the model base. |
| [`oihana/php-schema`](https://github.com/BcommeBois/oihana-php-schema) | Schema.org value objects: `org\schema\Thing`, `constants\Schema`, `helpers\SchemaResolver`. |
| [`oihana/php-signals`](https://github.com/BcommeBois/oihana-php-signals) | Signal/slot events behind the model lifecycle notices. |
| [`php-di/php-di`](https://packagist.org/packages/php-di/php-di) | PSR-11 DI container injected into every model. |
| [`matthiasmullie/scrapbook`](https://packagist.org/packages/matthiasmullie/scrapbook) | PSR-16 cache backend used by `CacheableTrait`. |
| [`psr/container`](https://packagist.org/packages/psr/container) | PSR-11 `ContainerInterface` contract. |
| [`psr/log`](https://packagist.org/packages/psr/log) | PSR-3 `LoggerInterface` contract. |
| [`psr/simple-cache`](https://packagist.org/packages/psr/simple-cache) | PSR-16 `CacheInterface` contract used for collection caching. |

## Development dependencies

| Package | Role |
|---|---|
| `phpunit/phpunit` | Test runner (strict mode). |
| `nunomaduro/collision` | Readable CLI error output. |
| `phpdocumentor/shim` | API documentation generation. |

## Next steps

- [Models](../models.md)
- [Documents](../documents.md)
