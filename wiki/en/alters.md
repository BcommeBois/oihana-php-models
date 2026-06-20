# Alters

Alters are a declarative, per-property transformation pipeline. Instead of writing imperative casting and cleaning code, you describe — in a plain associative array — which transformation(s) should be applied to each key of a document (array or object). The pipeline reads each property, runs the configured `Alter` type(s) on its value, and writes the result back. Definitions can be a single alter, an alter with parameters, or several alters chained together so the output of one becomes the input of the next.

The pipeline is provided by three traits, each exposing a different entry point but all sharing the same underlying alter engine (`AlterTrait`):

- `AlterDocumentTrait` — exposes `$alters` and `alter( $document )` to transform a full document, a list of documents, or an object.
- `AlterBindVarsTrait` — exposes `$bindAlters` and `alterBindVars( $bindVars, $context )` to transform a (context-scoped) bind-variables array, then clean it.
- `AlterTrait` — the engine itself (`alterProperty()`, chaining detection, dispatch), composed of one focused trait per alter type under `traits/alters/`.

Two small companion traits complete the system: `AlterKeyTrait` provides the default property key (`$alterKey`, defaults to `Schema::ID`) used by URL generation, and `AlterValueTrait` implements the fixed-value override.

## Alter types

Every alter type is a constant on the `oihana\models\enums\Alter` enum. The table below maps each type to its behaviour and the shape of its definition.

| Alter type | Definition shape | What it does |
|---|---|---|
| `Alter::ARRAY` | `[ Alter::ARRAY , ...subAlters ]` | Splits a `;`-separated string into an array, then applies the listed sub-alters to its elements (`CALL`, `CLEAN`, `FLOAT`, `GET`, `HYDRATE`, `INT`, `JSON_PARSE`, `NORMALIZE`, `NOT`). |
| `Alter::CALL` | `[ Alter::CALL , $callable , ...$args ]` | Invokes a callable as `fn( $value , ...$args )` and replaces the value with its return. Strings are resolved via `resolveCallable()`. |
| `Alter::CLEAN` | `Alter::CLEAN` | Removes empty (`""`) and unset elements from an array. |
| `Alter::FLOAT` | `Alter::FLOAT` | Casts the value to `float`, or each element to `float` if it is an array. |
| `Alter::GET` | `[ Alter::GET , $modelId , $key ]` | Replaces an identifier with a full document fetched through a Documents model resolved from the container (returns `null` on failure). |
| `Alter::HYDRATE` | `[ Alter::HYDRATE , Class::class , $normalize?, $flags? ]` | Normalizes (optional) then hydrates an array value into an instance of the given class (`Thing` subclasses use their constructor, others use reflection). Empty arrays become `null`. |
| `Alter::INT` | `Alter::INT` | Casts the value to `int`, or each element to `int` if it is an array. |
| `Alter::JSON_PARSE` | `[ Alter::JSON_PARSE , ...$jsonDecodeArgs ]` | Decodes a valid JSON string with `json_decode()`; non-JSON strings are left untouched. |
| `Alter::JSON_STRINGIFY` | `[ Alter::JSON_STRINGIFY , ...$jsonEncodeArgs ]` | Encodes the value to a JSON string with `json_encode()`. |
| `Alter::LISTIFY` | `[ Alter::LISTIFY , $separator?, $replace?, $default? ]` | Splits a string/array, trims and drops empties, then re-joins (defaults: split on `;`, join with `PHP_EOL`); falls back to `$default` if empty. |
| `Alter::MAP` | `[ Alter::MAP , $callable , ...$args ]` | Calls `fn( &$document , $container , $key , $value , $params )` — has access to the whole document, so it can compute a value from sibling properties. |
| `Alter::NORMALIZE` | `[ Alter::NORMALIZE , $flags? ]` | Normalizes the value with `normalize()` (default `CleanFlag::DEFAULT \| CleanFlag::RETURN_NULL`): trims, drops empties/nulls recursively. |
| `Alter::NOT` | `Alter::NOT` | Inverts a boolean (or every element of an array of booleans). |
| `Alter::URL` | `[ Alter::URL , $path , $property?, $containerKey?, $trailingSlash? ]` | Builds a URL by joining an (optional container-resolved) base URL, a path segment and a document property value. |
| `Alter::VALUE` | `[ Alter::VALUE , $newValue ]` | Overrides the property with a fixed value. |

> Note: the enum also declares `Alter::LIST`, which is reserved and currently has no dedicated handler (unknown alter types pass the value through unchanged).

## Transforming a document

Declare the rules in `$alters`, then call `alter()`:

```php
use oihana\models\enums\Alter;
use oihana\models\traits\AlterDocumentTrait;

class ProductMapper
{
    use AlterDocumentTrait;

    public function __construct()
    {
        $this->alters =
        [
            'price' => Alter::FLOAT,
            'tags'  => [ Alter::ARRAY , Alter::CLEAN ],
            'meta'  => [ Alter::JSON_PARSE ],
            'name'  => [ Alter::CALL , 'trim' ],
        ];
    }
}

$input =
[
    'price' => '19.99',
    'tags'  => 'foo;;bar;',
    'meta'  => '{"active":true}',
    'name'  => '  John  ',
];

$output = ( new ProductMapper() )->alter( $input );

// [
//     'price' => 19.99,
//     'tags'  => ['foo', 'bar'],
//     'meta'  => (object) [ 'active' => true ],
//     'name'  => 'John',
// ]
```

When the document is a sequential array (a list), `alter()` applies itself recursively to each element, so the same `$alters` definition works for one document or a collection.

## Chaining alterations

A property value can be `[ Alter::A , Alter::B , ... ]` to run several alters in sequence, or `[ Alter::A , [ Alter::B , ...args ] ]` to chain alters that take parameters. Each step receives the previous step's output.

```php
$this->alters =
[
    // split, then cast every element to float
    'prices' => [ Alter::ARRAY , Alter::FLOAT ],

    // normalize, then hydrate the cleaned array into an object
    'geo'    => [ Alter::NORMALIZE , [ Alter::HYDRATE , GeoCoordinates::class ] ],

    // computed value from sibling properties
    'total'  => [ Alter::MAP , fn( &$doc, $c, $k, $v, $p ) => $doc['price'] * ( 1 + ( $doc['vat'] ?? 0 ) ) ],
];
```

## Transforming bind variables

`AlterBindVarsTrait` applies the same engine to a bind-variables array, scoped by a context key, and runs `clean()` on the result:

```php
use oihana\models\enums\Alter;
use oihana\models\traits\AlterBindVarsTrait;

class Query
{
    use AlterBindVarsTrait;

    public function __construct()
    {
        $this->bindAlters =
        [
            'get' =>
            [
                'id'    => Alter::INT,
                'price' => Alter::FLOAT,
            ],
        ];
    }
}

$result = ( new Query() )->alterBindVars( [ 'id' => '42' , 'price' => '19.99' ] , 'get' );
// [ 'id' => 42 , 'price' => 19.99 ]
```

## Next steps

- [Documents](documents.md) — the model layer that consumes alters during read/write.
- [Models](models.md) — base models composing the alter traits.
- [Enumerations](enums.md) — the `Alter` enum and related parameter keys.
- [Tests & coverage](testing.md) — running the test suite.
