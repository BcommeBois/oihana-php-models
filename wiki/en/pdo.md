# PDO

`oihana\models\pdo\PDOModel` is a base model for relational data sources reached
through a [PDO](https://www.php.net/manual/en/book.pdo.php) connection. It wraps a
`PDO` instance, prepares and executes parameterised queries, binds named values
safely, and maps each row to a plain object or to a typed schema class. All the
query logic lives in the reusable `oihana\models\pdo\PDOTrait`, so you can mix it
into any class of your own.

## `PDOModel` — a ready-to-use model

`PDOModel` extends [`Model`](models.md) and adds the PDO layer. It is built from a
dependency-injection container plus an optional configuration array:

```php
use DI\Container;
use oihana\models\pdo\PDOModel;

$container = new Container() ;

$config =
[
    'deferAssignment' => true ,
    'pdo'             => 'my_pdo_service' , // a PDO instance or a container service id
    'schema'          => MyEntity::class ,  // optional schema class for row mapping
];

$model = new PDOModel( $container , $config ) ;

// Fetch a single record (named, bound parameters)
$user = $model->fetch( 'SELECT * FROM users WHERE id = :id' , [ 'id' => 123 ] ) ;

// Fetch every matching record
$users = $model->fetchAll( 'SELECT * FROM users WHERE active = :active' , [ 'active' => 1 ] ) ;
```

### Constructor

```php
public function __construct( Container $container , array $init = [] )
```

The `$init` array accepts the following optional keys (mirrored by the
`ModelParam` enum constants):

| Key | Constant | Role |
|---|---|---|
| `alters` | `ModelParam::ALTERS` | Alterations applied to every fetched row via `AlterDocumentTrait`. |
| `binds` | `ModelParam::BINDS` | Default bindings used by the query traits. |
| `deferAssignment` | `ModelParam::DEFER_ASSIGNMENT` | When `true` (and a schema is set), uses `FETCH_PROPS_LATE` so the constructor runs before properties are assigned. |
| `schema` | `ModelParam::SCHEMA` | Fully-qualified class name used as `FETCH_CLASS` mapping target. |
| `pdo` | `ModelParam::PDO` | A `PDO` instance, or a container service id resolving to one. |

The `pdo` value is resolved by `initializePDO()`: a string is looked up in the
container (`$container->has(...)` then `get(...)`); anything that is not a `PDO`
ends up as `null`.

## Query methods

These methods come from `PDOTrait`. Every one accepts named, bound parameters and
a final `bool $throwable` flag — when `false` (the default) exceptions are logged
and a neutral value is returned; when `true` the exception is re-thrown.

| Method | Returns | Role |
|---|---|---|
| `fetch( $query , $bindVars = [] , $throwable = false )` | `mixed\|null` | Run a SELECT and return the first row (object or schema instance), or `null`. |
| `fetchAll( $query , $bindVars = [] , $throwable = false )` | `array` | Return every matching row; empty array if none or on failure. |
| `fetchAllAsGenerator( $query , $bindVars = [] , $throwable = false )` | `Generator<object>` | Stream rows one by one — memory-friendly for large result sets. |
| `fetchColumn( $query , $bindVars = [] , $column = 0 , $throwable = false )` | `mixed` | Return one column (0-based) from the first row, or `null`. |
| `fetchColumnArray( $query , $bindVars = [] , $throwable = false )` | `array<int,string>` | Return a flat list built from the first column of every row. |

Supporting helpers:

| Method | Returns | Role |
|---|---|---|
| `bindValues( $statement , $bindVars = [] )` | `void` | Bind named parameters onto a prepared statement. |
| `initializeDefaultFetchMode( $statement )` | `void` | Apply `FETCH_ASSOC`, or `FETCH_CLASS` (`+ FETCH_PROPS_LATE`) when a schema is set. |
| `initializePDO( $init , $container = null )` | `static` | Resolve and store the `PDO` instance. |
| `initializeDeferAssignment( $init = [] )` | `static` | Read the `deferAssignment` flag from the init array. |
| `isConnected()` | `bool` | Report whether the underlying `PDO` connection is alive. |

### Binding values

`bindVars` is an associative array. A scalar is bound as-is; a two-element array
lets you pass an explicit PDO type:

```php
use PDO;

$rows = $model->fetchAll(
    'SELECT * FROM orders WHERE customer_id = :customer AND total >= :total' ,
    [
        'customer' => [ 42 , PDO::PARAM_INT ] , // value + explicit type
        'total'    => 100.0 ,                   // bound as-is
    ]
) ;
```

The leading `:` is added for you — pass `customer`, not `:customer`.

### Streaming large result sets

`fetchAllAsGenerator()` yields one altered object at a time and closes the cursor
when iteration ends, so a million-row export never materialises in memory:

```php
foreach ( $model->fetchAllAsGenerator( 'SELECT * FROM events ORDER BY id' ) as $event )
{
    process( $event ) ;
}
```

### Single-column queries

```php
$count  = $model->fetchColumn( 'SELECT COUNT(*) FROM users' ) ;        // scalar
$emails = $model->fetchColumnArray( 'SELECT email FROM users' ) ;       // ['a@x', 'b@y', ...]
```

### Schema mapping

When `schema` is a real class, rows are hydrated as instances of it through
`FETCH_CLASS`. Set `deferAssignment` to `true` to add `FETCH_PROPS_LATE`, which
runs the class constructor before column values are assigned — useful when your
constructor sets defaults the columns should override. Without a schema, rows are
returned as plain `stdClass` objects (`fetch`) or associative arrays (`fetchAll`).

### Error handling

By default a failed query is caught: under CLI the failure is printed (query,
bindings, message) and otherwise it is logged through the model's `warning()`
helper, then a neutral value is returned (`null` / `[]`). Pass `$throwable = true`
to bubble the exception up to your own handler instead.

## Next steps

- [Models](models.md) — the `Model` base class `PDOModel` extends.
- [Documents](documents.md) — document-oriented models for NoSQL sources.
- [Tests & coverage](testing.md) — how the model layer is tested.
