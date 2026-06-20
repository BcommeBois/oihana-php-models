# Dépendances

`oihana/php-models` conserve une empreinte runtime ciblée. Voici ce dont elle a
besoin et **pourquoi**.

## Dépendances runtime

| Paquet | Rôle |
|---|---|
| [`oihana/php-core`](https://github.com/BcommeBois/oihana-php-core) | Helpers et utilitaires de base (tableaux, chaînes, résolution de conteneur). |
| [`oihana/php-traits`](https://github.com/BcommeBois/oihana-php-traits) | Traits d'objets réutilisables (`ConfigTrait`, `ContainerTrait`, `KeyValueTrait`, …) sur lesquels les modèles s'appuient. |
| [`oihana/php-enums`](https://github.com/BcommeBois/oihana-php-enums) | Énumérations de constantes typées (`Char`, `Order`, …). |
| [`oihana/php-reflect`](https://github.com/BcommeBois/oihana-php-reflect) | `ConstantsTrait` et utilitaires de réflexion/hydratation. |
| [`oihana/php-exceptions`](https://github.com/BcommeBois/oihana-php-exceptions) | Types d'exceptions partagés levés par les modèles. |
| [`oihana/php-files`](https://github.com/BcommeBois/oihana-php-files) | Helpers de fichiers utilisés par les helpers de document/URL. |
| [`oihana/php-logging`](https://github.com/BcommeBois/oihana-php-logging) | Journalisation PSR-3 (`Logger`, `DebugTrait`) utilisée par le modèle de base. |
| [`oihana/php-schema`](https://github.com/BcommeBois/oihana-php-schema) | Objets valeur Schema.org : `org\schema\Thing`, `constants\Schema`, `helpers\SchemaResolver`. |
| [`oihana/php-signals`](https://github.com/BcommeBois/oihana-php-signals) | Événements signal/slot derrière les notices de cycle de vie. |
| [`php-di/php-di`](https://packagist.org/packages/php-di/php-di) | Conteneur DI PSR-11 injecté dans chaque modèle. |
| [`matthiasmullie/scrapbook`](https://packagist.org/packages/matthiasmullie/scrapbook) | Backend de cache PSR-16 utilisé par `CacheableTrait`. |
| [`psr/container`](https://packagist.org/packages/psr/container) | Contrat PSR-11 `ContainerInterface`. |
| [`psr/log`](https://packagist.org/packages/psr/log) | Contrat PSR-3 `LoggerInterface`. |
| [`psr/simple-cache`](https://packagist.org/packages/psr/simple-cache) | Contrat PSR-16 `CacheInterface` utilisé pour la mise en cache de collections. |

## Dépendances de développement

| Paquet | Rôle |
|---|---|
| `phpunit/phpunit` | Lanceur de tests (mode strict). |
| `nunomaduro/collision` | Sortie d'erreurs CLI lisible. |
| `phpdocumentor/shim` | Génération de la documentation d'API. |

## Étapes suivantes

- [Modèles](../models.md)
- [Documents](../documents.md)
