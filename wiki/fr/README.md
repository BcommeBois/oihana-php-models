# oihana/php-models — couche de modèles documentaires pour PHP

![Langue](https://img.shields.io/badge/langue-Français-blue)

`oihana/php-models` est une bibliothèque PHP 8.4+ offrant une **couche de modèles documentaires** : un `Model` de base intégré à un conteneur d'injection de dépendances, une couche CRUD documentaire composable (`DocumentsTrait`), des modèles adossés à PDO, un pipeline déclaratif de transformation de propriétés (**alters**), une mise en **cache** de collections via PSR-16, et des **signaux & notices** sur le cycle de vie des modèles.

![Oihana PHP Models](https://raw.githubusercontent.com/BcommeBois/oihana-php-models/main/assets/images/oihana-php-models-logo-inline-512x160.png)

## À qui s'adresse cette documentation

Aux développeuses et développeurs PHP qui veulent :

- modéliser des documents avec une surface **CRUD** unique et composable — `DocumentsTrait` ;
- interroger des sources relationnelles via **PDO** avec des paramètres liés — `PDOModel`, `PDOTrait` ;
- **transformer** déclarativement les propriétés d'un document (cast, hydratation, map, normalisation…) — `AlterDocumentTrait` + l'énumération `Alter` ;
- mettre en **cache** des collections derrière un stockage PSR-16 — `CacheableTrait` ;
- réagir aux **événements** du cycle de vie (avant/après insert, update, delete…) — les traits `Has*Signals` + les `notices` ;
- bâtir des modèles **compatibles Schema.org** sur `org\schema\Thing` — `SchemaTrait`.

## Démarrage rapide

```php
use DI\Container;
use oihana\models\pdo\PDOModel;

$container = new Container();

$model = new PDOModel( $container ,
[
    'pdo'    => 'my_pdo_service', // une instance PDO ou un identifiant de service du conteneur
    'schema' => MyEntity::class,
]);

$record  = $model->fetch( 'SELECT * FROM users WHERE id = :id' , [ 'id' => 123 ] );
$records = $model->fetchAll( 'SELECT * FROM users' );
```

Pour tous les détails (options, contrats, énumérations), voir le sommaire ci-dessous.

## Sommaire

### Démarrage — [`getting-started/`](getting-started/)

- [Introduction](getting-started/introduction.md) — ce que fait la bibliothèque et la philosophie *oihana*.
- [Installation](getting-started/installation.md) — prérequis PHP 8.4+ et `composer require`.
- [Dépendances](getting-started/dependencies.md) — les paquets runtime et leur rôle.

### Utilisation

- [Modèles](models.md) — le `Model` de base, `ModelTrait`, et les interfaces CRUD.
- [Documents](documents.md) — `DocumentsTrait` : list, get, count, insert, update, delete et plus.
- [PDO](pdo.md) — `PDOModel` et `PDOTrait` pour les sources relationnelles.
- [Alters](alters.md) — le pipeline déclaratif de transformation de propriétés.
- [Cache](cache.md) — `CacheableTrait` et la mise en cache de collections via PSR-16.
- [Signaux & notices](signals-notices.md) — les événements du cycle de vie.
- [Énumérations](enums.md) — `Alter`, `ModelParam`, `NoticeType`.
- [Helpers](helpers.md) — les fonctions libres autochargées.

### Transversal

- [Tests & couverture](testing.md) — lancer la suite PHPUnit et mesurer la couverture.

## Code source

Le code de la bibliothèque vit sous [`src/oihana/models/`](../../src/oihana/models/) — espace de noms `oihana\models`.

## Voir aussi

- [Packagist `oihana/php-models`](https://packagist.org/packages/oihana/php-models) — la page du paquet.
- [Référence d'API (phpDocumentor)](https://bcommebois.github.io/oihana-php-models) — référence générée au niveau des classes.
