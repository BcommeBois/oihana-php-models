# Models

La couche modèle de `oihana/php-models` : une petite classe de base `Model`, les
contrats CRUD exprimés sous forme d'interfaces fines, et un ensemble de traits
composables (`ModelTrait`, `SchemaTrait`, `PropertyTrait`, `ThrowableTrait`) que
vous intégrez dans vos propres modèles.

## `Model` — la classe de base

`oihana\models\Model` est la fondation minimale sur laquelle repose chaque
modèle. Elle conserve une référence au
[conteneur](getting-started/introduction.md) d'injection de dépendances et
configure le débogage, la journalisation et le comportement mock à partir d'un
tableau d'initialisation.

```php
use DI\Container;
use oihana\models\Model;

$container = new Container() ;

$model = new Model( $container , [
    'debug'  => true ,
    'logger' => 'my_logger_service' , // un LoggerInterface ou son identifiant dans le conteneur
    'mock'   => false ,
] ) ;
```

### Constructeur

```php
public function __construct( Container $container , array $init = [] )
```

- `$container` — le conteneur PHP-DI utilisé pour résoudre des services tels que le logger.
- `$init` — un tableau associatif optionnel d'options :

| Clé | Type | Rôle |
|---|---|---|
| `debug` | `bool` | Active le mode débogage (défaut `false`). |
| `logger` | `LoggerInterface\|string\|null` | Une instance de logger PSR-3, ou l'identifiant d'un logger enregistré dans le conteneur. |
| `mock` | `bool` | Active le comportement mock (défaut `false`). |

La classe compose `DebugTrait` et `ToStringTrait`, et expose le conteneur via sa
propriété publique `$container`.

## Interfaces de modèle — les contrats CRUD

Les opérations sont réparties en une interface par verbe. Cela permet à un
modèle de déclarer *exactement* les capacités qu'il supporte plutôt que
d'implémenter un contrat monolithique. Toutes les méthodes acceptent un tableau
d'options `$init` optionnel et vivent dans l'espace de noms
`oihana\models\interfaces`.

| Interface | Méthode | Retour | Rôle |
|---|---|---|---|
| `CountModel` | `count( array $init = [] )` | `int` | Compte les documents correspondant aux critères. |
| `ExistModel` | `exist( array $init = [] )` | `bool` | Indique si un document existe pour les critères. |
| `GetModel` | `get( array $init = [] )` | `mixed` | Récupère un seul document ou valeur (étend `ExistModel`). |
| `ListModel` | `list( array $init = [] )` | `array` | Charge tous les documents correspondants dans un tableau. |
| `StreamModel` | `stream( array $init = [] )` | `Generator` | Émet les documents un à un pour les grands jeux de données. |
| `LastModel` | `last( array $init = [] )` | `mixed` | Le dernier document correspondant aux critères (étend `ExistModel`). |
| `InsertModel` | `insert( array $init = [] )` | `mixed` | Insère un nouveau document. |
| `UpdateModel` | `update( array $init = [] )` | `mixed` | Met à jour les champs d'un document existant. |
| `UpdateDateModel` | `updateDate( array $init = [] , string $property = Schema::MODIFIED )` | `mixed` | Affecte la date courante à une propriété date (défaut `modified`). |
| `ReplaceModel` | `replace( array $init = [] )` | `mixed` | Remplace un document existant. |
| `UpsertModel` | `upsert( array $init = [] )` | `mixed` | Insère, ou met à jour/remplace s'il existe déjà. |
| `DeleteModel` | `delete( array $init = [] )` | `null\|array\|object` | Supprime un ou plusieurs documents. |
| `DeleteAllModel` | `deleteAll( array $init = [] )` | `mixed` | Supprime un ensemble de documents. |
| `TruncateModel` | `truncate( array $init = [] )` | `mixed` | Retire tous les documents du backend. |

### `DocumentsModel` — le contrat complet

`oihana\models\interfaces\DocumentsModel` agrège les interfaces CRUD courantes
en un unique contrat indépendant du stockage. Les implémentations ciblent
n'importe quel backend — ArangoDB, SQL OpenEdge, un mock en mémoire, etc.

```php
use oihana\models\interfaces\DocumentsModel;

function harvest( DocumentsModel $model ) : void
{
    if ( ! $model->exist( [ 'binds' => [ 'id' => 123 ] ] ) )
    {
        $model->insert( [ 'value' => [ 'id' => 123 , 'name' => 'Acme' ] ] ) ;
    }

    foreach ( $model->stream( [ 'sort' => 'name' ] ) as $document )
    {
        // traite chaque document sans charger tout le jeu en mémoire
    }
}
```

`DocumentsModel` étend : `CountModel`, `DeleteModel`, `ExistModel`,
`GetModel`, `InsertModel`, `LastModel`, `ListModel`, `ReplaceModel`,
`StreamModel`, `UpdateModel`, `UpdateDateModel`, `UpsertModel`, `TruncateModel`.

## Traits de support

Ces traits ajoutent une responsabilité unique et ciblée à une classe. Chacun
fournit une méthode `initializeXxx()` qui lit une valeur dans un tableau `$init`
et retourne `$this` pour le chaînage.

### `ModelTrait` — porter un sous-modèle

`oihana\models\traits\ModelTrait` dote une classe d'une propriété
`DocumentsModel $model` et des aides pour l'initialiser et la garder. Utile pour
les contrôleurs ou services qui délèguent la persistance à un modèle résolu
depuis le conteneur.

```php
use oihana\models\traits\ModelTrait;

class ProductService
{
    use ModelTrait ;

    public function boot() : void
    {
        $this->initializeModel( [ 'model' => 'products.model' ] ) ; // identifiant dans le conteneur
        $this->assertModel() ; // lève UnexpectedValueException si non défini
    }
}
```

- `initializeModel( array $init = [] )` — résout la clé `model` (un
  `DocumentsModel` ou son identifiant dans le conteneur) dans `$this->model`.
- `assertModel()` — lève `UnexpectedValueException` si `$this->model` n'est pas défini.

### `SchemaTrait` — résoudre un schéma d'hydratation

`oihana\models\traits\SchemaTrait` porte le schéma utilisé pour hydrater les
ressources. Le schéma peut être un nom de classe (`string`), une `Closure`, ou
un `org\schema\helpers\SchemaResolver`.

```php
use oihana\models\traits\SchemaTrait;

class CatalogModel
{
    use SchemaTrait ;
}

$model = new CatalogModel() ;
$model->initializeSchema( [ 'schema' => Product::class ] ) ;

$model->hasSchema() ;        // true
$model->getSchema() ;        // 'Product'
$model->getSchema( $target ) ; // valeur résolue quand le schéma est une Closure / un SchemaResolver
```

- `initializeSchema( array $init = [] )` — lit la clé `schema` ; lève
  `InvalidArgumentException` si la valeur n'est pas une `string`, une `Closure`
  ou un `SchemaResolver`.
- `hasSchema()` — `true` lorsqu'un schéma est défini.
- `getSchema( mixed $target = null )` — retourne la chaîne de schéma résolue, en
  invoquant la `Closure` / le `SchemaResolver` avec `$target` le cas échéant.

### `PropertyTrait` — une référence de propriété nommée

`oihana\models\traits\PropertyTrait` stocke une unique `?string $property`,
typiquement une clé ou un nom de champ au sein d'un document.

```php
use oihana\models\traits\PropertyTrait;

class FieldModel
{
    use PropertyTrait ;
}

$model = new FieldModel() ;
$model->initializeProperty( [ 'property' => 'name' ] ) ;
echo $model->property ; // "name"

$model->assertProperty() ; // lève UnexpectedValueException si non défini
```

La clé d'initialisation est exposée via la constante `PropertyTrait::PROPERTY`
(`'property'`).

### `ThrowableTrait` — opter pour les exceptions

`oihana\models\traits\ThrowableTrait` laisse un modèle décider si ses méthodes
lèvent une exception en cas d'erreur ou échouent silencieusement.

```php
use oihana\models\traits\ThrowableTrait;

class SafeModel
{
    use ThrowableTrait ;
}

$model = new SafeModel() ;
$model->initializeThrowable( [ 'throwable' => true ] ) ;

$model->throwable ; // true
```

La clé d'initialisation est exposée via la constante `ThrowableTrait::THROWABLE`
(`'throwable'`) et vaut `false` par défaut.

## Étapes suivantes

- [Documents](documents.md) — modèles de document concrets bâtis sur ces contrats.
- [PDO](pdo.md) — le modèle adossé à PDO et les aides base de données.
- [Enumerations](enums.md) — `ModelParam` et les autres clés d'options.
- [Tests & couverture](testing.md) — exécuter la suite de tests.
