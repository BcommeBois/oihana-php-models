# Models

The model layer of `oihana/php-models`: a small base `Model` class, the CRUD
contracts expressed as fine-grained interfaces, and a set of composable traits
(`ModelTrait`, `SchemaTrait`, `PropertyTrait`, `ThrowableTrait`) you mix into
your own models.

## `Model` — the base class

`oihana\models\Model` is the minimal foundation every model builds on. It holds
a reference to the DI [container](getting-started/introduction.md), and wires up
debug, logging and mock behaviour from an initialization array.

```php
use DI\Container;
use oihana\models\Model;

$container = new Container() ;

$model = new Model( $container , [
    'debug'  => true ,
    'logger' => 'my_logger_service' , // a LoggerInterface or its container id
    'mock'   => false ,
] ) ;
```

### Constructor

```php
public function __construct( Container $container , array $init = [] )
```

- `$container` — the PHP-DI container used to resolve services such as the logger.
- `$init` — an optional associative array of options:

| Key | Type | Role |
|---|---|---|
| `debug` | `bool` | Enables debug mode (default `false`). |
| `logger` | `LoggerInterface\|string\|null` | A PSR-3 logger instance, or the id of one registered in the container. |
| `mock` | `bool` | Enables mock behaviour (default `false`). |

The class composes `DebugTrait` and `ToStringTrait`, and exposes the container
through its public `$container` property.

## Model interfaces — the CRUD contracts

Operations are split into one interface per verb. This lets a model declare
*exactly* the capabilities it supports rather than implementing a monolithic
contract. All methods accept an optional `$init` array of options and live in
the `oihana\models\interfaces` namespace.

| Interface | Method | Returns | Role |
|---|---|---|---|
| `CountModel` | `count( array $init = [] )` | `int` | Count documents matching the criteria. |
| `ExistModel` | `exist( array $init = [] )` | `bool` | Whether a document exists for the criteria. |
| `GetModel` | `get( array $init = [] )` | `mixed` | Retrieve a single document or value (extends `ExistModel`). |
| `ListModel` | `list( array $init = [] )` | `array` | Load all matching documents into an array. |
| `StreamModel` | `stream( array $init = [] )` | `Generator` | Yield documents one at a time for large datasets. |
| `LastModel` | `last( array $init = [] )` | `mixed` | The last document matching the criteria (extends `ExistModel`). |
| `InsertModel` | `insert( array $init = [] )` | `mixed` | Insert a new document. |
| `UpdateModel` | `update( array $init = [] )` | `mixed` | Update fields of an existing document. |
| `UpdateDateModel` | `updateDate( array $init = [] , string $property = Schema::MODIFIED )` | `mixed` | Set a date property to the current date (default `modified`). |
| `ReplaceModel` | `replace( array $init = [] )` | `mixed` | Replace an existing document. |
| `UpsertModel` | `upsert( array $init = [] )` | `mixed` | Insert, or update/replace if it already exists. |
| `DeleteModel` | `delete( array $init = [] )` | `null\|array\|object` | Delete one or more documents. |
| `DeleteAllModel` | `deleteAll( array $init = [] )` | `mixed` | Delete a set of documents. |
| `TruncateModel` | `truncate( array $init = [] )` | `mixed` | Remove every document from the backend. |

### `DocumentsModel` — the full contract

`oihana\models\interfaces\DocumentsModel` aggregates the common CRUD interfaces
into a single, storage-agnostic contract. Implementations target any backend —
ArangoDB, OpenEdge SQL, an in-memory mock, etc.

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
        // process each document without loading the whole set in memory
    }
}
```

`DocumentsModel` extends: `CountModel`, `DeleteModel`, `ExistModel`,
`GetModel`, `InsertModel`, `LastModel`, `ListModel`, `ReplaceModel`,
`StreamModel`, `UpdateModel`, `UpdateDateModel`, `UpsertModel`, `TruncateModel`.

## Supporting traits

These traits add a single, focused concern to a class. Each ships an
`initializeXxx()` method that reads a value from an `$init` array and returns
`$this` for chaining.

### `ModelTrait` — hold a sub-model

`oihana\models\traits\ModelTrait` gives a class a `DocumentsModel $model`
property and the helpers to initialize and guard it. Useful for controllers or
services that delegate persistence to a model resolved from the container.

```php
use oihana\models\traits\ModelTrait;

class ProductService
{
    use ModelTrait ;

    public function boot() : void
    {
        $this->initializeModel( [ 'model' => 'products.model' ] ) ; // container id
        $this->assertModel() ; // throws UnexpectedValueException if unset
    }
}
```

- `initializeModel( array $init = [] )` — resolves the `model` key (a
  `DocumentsModel` or its container id) into `$this->model`.
- `assertModel()` — throws `UnexpectedValueException` if `$this->model` is not set.

### `SchemaTrait` — resolve a hydration schema

`oihana\models\traits\SchemaTrait` carries the schema used to hydrate resources.
The schema may be a class name (`string`), a `Closure`, or a
`org\schema\helpers\SchemaResolver`.

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
$model->getSchema( $target ) ; // resolved value when schema is a Closure / SchemaResolver
```

- `initializeSchema( array $init = [] )` — reads the `schema` key; throws
  `InvalidArgumentException` if the value is not a `string`, `Closure` or
  `SchemaResolver`.
- `hasSchema()` — `true` when a schema is set.
- `getSchema( mixed $target = null )` — returns the resolved schema string,
  invoking the `Closure` / `SchemaResolver` with `$target` when applicable.

### `PropertyTrait` — a named property reference

`oihana\models\traits\PropertyTrait` stores a single `?string $property`,
typically a key or field name within a document.

```php
use oihana\models\traits\PropertyTrait;

class FieldModel
{
    use PropertyTrait ;
}

$model = new FieldModel() ;
$model->initializeProperty( [ 'property' => 'name' ] ) ;
echo $model->property ; // "name"

$model->assertProperty() ; // throws UnexpectedValueException if unset
```

The init key is exposed as the `PropertyTrait::PROPERTY` constant (`'property'`).

### `ThrowableTrait` — opt into exceptions

`oihana\models\traits\ThrowableTrait` lets a model decide whether its methods
throw on error or fail silently.

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

The init key is exposed as the `ThrowableTrait::THROWABLE` constant
(`'throwable'`), and defaults to `false`.

## Next steps

- [Documents](documents.md) — concrete document models built on these contracts.
- [PDO](pdo.md) — the PDO-backed model and database helpers.
- [Enumerations](enums.md) — `ModelParam` and the other option keys.
- [Tests & coverage](testing.md) — running the suite.
