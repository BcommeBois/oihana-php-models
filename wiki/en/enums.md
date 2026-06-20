# Enumerations

Recurring keys, operation names and lifecycle signals are exposed as **typed
constants** grouped in helper classes, instead of *magic strings* scattered
through the codebase. These classes are *not* native PHP `enum`s: each one uses
`oihana\reflect\traits\ConstantsTrait`, so the constants stay plain `string`
values you can pass anywhere, while still being introspectable
(`::getConstants()`, `::getConstant()`, `::includes()`…).

## `Alter`

`oihana\models\enums\Alter` — the catalogue of transformation or filter
operations that can be applied to an object property or an array key when
normalizing data inside models or collections. See [Alters](alters.md) for how
these operations are declared and executed.

| Constant | Value | Meaning |
|---|---|---|
| `Alter::ARRAY` | `'array'` | Cast / wrap the value into an array. |
| `Alter::CALL` | `'call'` | Apply a custom callback to the value. |
| `Alter::CLEAN` | `'clean'` | Remove empty / null entries from the value. |
| `Alter::FLOAT` | `'float'` | Cast the value to a float. |
| `Alter::GET` | `'get'` | Extract a value through a getter. |
| `Alter::HYDRATE` | `'hydrate'` | Hydrate the value into a typed object. |
| `Alter::LIST` | `'list'` | Treat the value as a list. |
| `Alter::INT` | `'int'` | Cast the value to an integer. |
| `Alter::JSON_PARSE` | `'jsonParse'` | Decode a JSON string into a value. |
| `Alter::JSON_STRINGIFY` | `'jsonStringify'` | Encode the value as a JSON string. |
| `Alter::LISTIFY` | `'listify'` | Turn the value into a normalized list. |
| `Alter::MAP` | `'map'` | Map each item of the value through a transform. |
| `Alter::NORMALIZE` | `'normalize'` | Normalize the value. |
| `Alter::NOT` | `'not'` | Negate / invert the value. |
| `Alter::URL` | `'url'` | Build or normalize a URL from the value. |
| `Alter::VALUE` | `'value'` | Replace with a fixed value. |

```php
use oihana\models\enums\Alter;

$alters =
[
    'price'   => [ Alter::FLOAT ] ,
    'tags'    => [ Alter::JSON_PARSE ] ,
    'authors' => [ Alter::CLEAN ] ,
];
```

## `ModelParam`

`oihana\models\enums\ModelParam` — the keys accepted by model constructors and
configuration arrays. The constants live in
`oihana\models\enums\traits\ModelParamTrait` and are exposed through the
`ModelParam` class (which also pulls in `ConstantsTrait` for introspection),
so the trait can be reused by more specialized parameter enumerations.

| Constant | Value | Meaning |
|---|---|---|
| `ModelParam::ALTER_KEY` | `'alterKey'` | Default identifier of the `alter` key. |
| `ModelParam::ALTERS` | `'alters'` | The alteration rules to apply. |
| `ModelParam::BINDS` | `'binds'` | Values bound to a query. |
| `ModelParam::BINDS_ALTERS` | `'bindsAlters'` | Alterations applied to bound values. |
| `ModelParam::CACHE` | `'cache'` | The cache backend / configuration. |
| `ModelParam::CONDITIONS` | `'conditions'` | Query conditions. |
| `ModelParam::DEBUG` | `'debug'` | Toggle debug behaviour. |
| `ModelParam::DEFAULT` | `'default'` | Default value. |
| `ModelParam::DEFER_ASSIGNMENT` | `'deferAssignment'` | Defer property assignment. |
| `ModelParam::ENFORCE` | `'enforce'` | Enforce a constraint. |
| `ModelParam::ENSURE` | `'ensure'` | Ensure a value / state. |
| `ModelParam::ID` | `'id'` | The identifier. |
| `ModelParam::KEY` | `'key'` | A single key. |
| `ModelParam::KEYS` | `'keys'` | A set of keys. |
| `ModelParam::LIST` | `'list'` | A list value. |
| `ModelParam::LOGGABLE` | `'loggable'` | The loggable flag / object. |
| `ModelParam::LOGGER` | `'logger'` | The logger instance. |
| `ModelParam::MOCK` | `'mock'` | Mock data / mode. |
| `ModelParam::MODEL` | `'model'` | The model definition. |
| `ModelParam::OPTIMIZED` | `'optimized'` | Optimized mode flag. |
| `ModelParam::OWNER` | `'owner'` | The owner reference. |
| `ModelParam::PDO` | `'pdo'` | The PDO connection. |
| `ModelParam::QUERY` | `'query'` | The query. |
| `ModelParam::QUERY_BUILDER` | `'queryBuilder'` | The query builder. |
| `ModelParam::QUERY_FIELDS` | `'queryFields'` | Fields selected by the query. |
| `ModelParam::QUERY_ID` | `'queryId'` | The query identifier. |
| `ModelParam::SCHEMA` | `'schema'` | The schema definition. |
| `ModelParam::SORT` | `'sort'` | The sort expression. |
| `ModelParam::SORT_DEFAULT` | `'sortDefault'` | The default sort expression. |
| `ModelParam::THROWABLE` | `'throwable'` | Whether to throw on error. |
| `ModelParam::TTL` | `'ttl'` | The cache time-to-live. |
| `ModelParam::VALUE` | `'value'` | A value. |

```php
use oihana\models\enums\ModelParam;

$init =
[
    ModelParam::SCHEMA => MyDocument::class ,
    ModelParam::ALTERS => [ 'price' => [ 'float' ] ] ,
    ModelParam::DEBUG  => true ,
];
```

## `NoticeType`

`oihana\models\enums\NoticeType` — the lifecycle signals emitted around model
mutations, used to dispatch `before*` / `after*` notifications. See
[Signals & notices](signals-notices.md) for how listeners subscribe to them.

| Constant | Value | Meaning |
|---|---|---|
| `NoticeType::BEFORE_INSERT` | `'beforeInsert'` | Emitted before an insert. |
| `NoticeType::AFTER_INSERT` | `'afterInsert'` | Emitted after an insert. |
| `NoticeType::BEFORE_UPDATE` | `'beforeUpdate'` | Emitted before an update. |
| `NoticeType::AFTER_UPDATE` | `'afterReplace'` | Emitted after an update. |
| `NoticeType::BEFORE_REPLACE` | `'beforeReplace'` | Emitted before a replace. |
| `NoticeType::AFTER_REPLACE` | `'afterReplace'` | Emitted after a replace. |
| `NoticeType::BEFORE_DELETE` | `'beforeDelete'` | Emitted before a delete. |
| `NoticeType::AFTER_DELETE` | `'afterDelete'` | Emitted after a delete. |
| `NoticeType::BEFORE_UPSERT` | `'beforeUpsert'` | Emitted before an upsert. |
| `NoticeType::AFTER_UPSERT` | `'afterUpsert'` | Emitted after an upsert. |
| `NoticeType::BEFORE_TRUNCATE` | `'beforeTruncate'` | Emitted before a truncate. |
| `NoticeType::AFTER_TRUNCATE` | `'afterTruncate'` | Emitted after a truncate. |

> Note: `NoticeType::AFTER_UPDATE` currently resolves to `'afterReplace'`, the
> same value as `NoticeType::AFTER_REPLACE` — listeners distinguishing the two
> should rely on the `before*` signals.

```php
use oihana\models\enums\NoticeType;

if ( NoticeType::includes( $type ) )
{
    match ( $type )
    {
        NoticeType::BEFORE_INSERT => $this->validate( $document ) ,
        NoticeType::AFTER_DELETE  => $this->purgeCache( $document ) ,
        default                   => null ,
    };
}
```

## Next steps

- [Alters](alters.md)
- [Models](models.md)
- [Signals & notices](signals-notices.md)
- [Tests & coverage](testing.md)
