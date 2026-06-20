# Documents

The document layer is a set of composable traits that turn a class into a full
CRUD model over a storage backend (ArangoDB, OpenEdge SQL, in-memory, …). Every
operation shares the same shape: it accepts a single `array $init = []` of
options and returns a type that depends on the action. `DocumentsTrait` wires the
model into a DI container and adds existence assertions, while smaller traits
(`ConditionsTrait`, `ListModelTrait`, `EnsureKeysTrait`, `BindsTrait`) provide
the reusable building blocks every model composes.

## The `DocumentsModel` contract

`oihana\models\interfaces\DocumentsModel` groups all the CRUD-like operations
behind a single contract. Each method takes an optional `$init` options array,
so callers stay uniform regardless of the backend.

| Method | Returns | Role |
|---|---|---|
| `count( array $init = [] )` | `int` | Count the documents matching the criteria. |
| `exist( array $init = [] )` | `bool` | Check whether a document exists for the criteria. |
| `get( array $init = [] )` | `mixed` | Retrieve a single document or value. |
| `list( array $init = [] )` | `array` | List documents matching filtering / sorting criteria. |
| `last( array $init = [] )` | `mixed` | Get the last document matching the options. |
| `stream( array $init = [] )` | `Generator` | Iterate over documents lazily. |
| `insert( array $init = [] )` | `mixed` | Insert a new document. |
| `update( array $init = [] )` | `mixed` | Update fields of an existing document. |
| `updateDate( array $init = [], string $property = Schema::MODIFIED )` | `mixed` | Stamp a single date property with the current date. |
| `replace( array $init = [] )` | `mixed` | Replace an existing document entirely. |
| `upsert( array $init = [] )` | `mixed` | Insert or update depending on existence. |
| `delete( array $init = [] )` | `null\|array\|object` | Delete one or more documents. |
| `truncate( array $init = [] )` | `mixed` | Remove every document from the storage. |

```php
use oihana\models\interfaces\DocumentsModel;

/** @var DocumentsModel $model */
$total = $model->count() ;
$user  = $model->get( [ 'binds' => [ 'id' => 42 ] ] ) ;
$page  = $model->list( [ 'sort' => 'name' ] ) ;
$model->upsert( [ 'value' => [ 'id' => 42 , 'status' => 'active' ] ] ) ;
$model->delete( [ 'binds' => [ 'id' => 42 ] ] ) ;
```

## `DocumentsTrait` — container wiring & assertions

`oihana\models\traits\DocumentsTrait` uses `ContainerTrait`. It resolves a
`DocumentsModel` (instance or DI service id) and asserts that a referenced
document actually exists.

```php
use oihana\models\traits\DocumentsTrait;

class ProductService
{
    use DocumentsTrait ;
}
```

### `getDocumentsModel()`

Returns a `DocumentsModel`, resolving it from the container when a service id
string is passed. Returns `null` if it cannot be resolved to a `DocumentsModel`.

```php
$products = $service->getDocumentsModel( 'products.model' ) ; // from container
$products = $service->getDocumentsModel( $existingModel ) ;    // pass-through
```

### `assertExistInModel()`

Asserts that a document exists in an `ExistModel`, throwing `Error404` otherwise.
It reads the lookup key (default `id`) from an object or accepts a scalar id, and
delegates to `$model->exist( [ 'binds' => [ $key => $id ] ] )`.

```php
$service->assertExistInModel( $document , $model , 'product' ) ;        // key 'id'
$service->assertExistInModel( 42 , $model , 'product' ) ;               // scalar id
$service->assertExistInModel( $edge , $model , 'product' , '_key' ) ;   // custom key
```

## `ConditionsTrait` — filtering rules

Holds a flexible `$conditions` array (WHERE / FILTER clauses, logical
constraints, driver-specific definitions) and hydrates it from the
`conditions` option.

```php
use oihana\models\traits\ConditionsTrait;

class MyModel
{
    use ConditionsTrait ;
}

$model = ( new MyModel() )->initializeConditions
([
    'conditions' => [ 'status' => 'active' ] ,
]) ;

$model->conditions ; // [ 'status' => 'active' ]
```

`initializeConditions()` returns `$this` for fluent chaining; a missing key
resets `$conditions` to an empty array.

## `ListModelTrait` — a `ListModel` reference

Adds an optional `$list` (`ListModel`) property, resolves it from the container
and guards its presence.

```php
use oihana\models\traits\ListModelTrait;

class MyModel
{
    use ListModelTrait ;

    public function __construct( array $init , ContainerInterface $container )
    {
        $this->initializeListModel( $init , $container ) ;
    }
}
```

- `initializeListModel( array $init = [], ?ContainerInterface $container = null )`
  reads the `list` option; a string is resolved through the container.
- `assertListModel()` throws `UnexpectedValueException` when `$list` is unset.

## `EnsureKeysTrait` — guarantee keys with defaults

Guarantees that given keys exist on a document (or on every item of an indexed
collection), filling missing ones with a default value. Configuration comes from
the `ensure` option or the `$ensure` instance property.

```php
use oihana\models\traits\EnsureKeysTrait;

class MyModel
{
    use EnsureKeysTrait ;

    public function process( array &$data , array $init = [] ) : void
    {
        $this->ensureDocumentKeys( $data , $init ) ;
    }
}

$data = [ 'id' => 1 ] ;
$model->process( $data ,
[
    'ensure' =>
    [
        'keys'    => [ 'status' ] ,
        'default' => 'draft' ,
        'enforce' => false ,
    ]
]) ;
// $data => [ 'id' => 1 , 'status' => 'draft' ]
```

A shorthand `'ensure' => [ 'status' , 'tags' ]` is accepted: keys only,
`default` is `null` and `enforce` is `false`. `initializeEnsure()` stores the
config on the instance for reuse and returns `$this`.

## `BindsTrait` — PDO bind parameters

Manages the default bind values used in PDO statements and merges them with
runtime binds.

```php
use oihana\models\traits\BindsTrait;

class MyModel
{
    use BindsTrait ;
}

$model = new MyModel() ;
$model->binds = [ 'id' => 42 ] ;

$params = $model->prepareBindVars( [ 'binds' => [ 'status' => 'active' ] ] ) ;
// [ 'id' => 42 , 'status' => 'active' ]
```

- `$binds` — the default bind map (defaults to `[]`).
- `BindsTrait::BINDS` — the `'binds'` option key constant.
- `initializeBinds()` overwrites `$binds` from the `binds` option (returns `$this`).
- `prepareBindVars()` returns the defaults merged with runtime binds (runtime wins).

## Next steps

- [Models](models.md) — the model base classes that assemble these traits.
- [PDO](pdo.md) — the SQL-backed document model and connection handling.
- [Alters](alters.md) — post-fetch document transformation pipelines.
- [Signals & notices](signals-notices.md) — observe model lifecycle events.
- [Tests & coverage](testing.md) — run the suite and check coverage.
