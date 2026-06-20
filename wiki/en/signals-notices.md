# Signals & notices

Every model lifecycle operation (insert, update, delete, replace, truncate,
upsert) exposes a pair of **before/after** hooks. A `Has*Signals` trait adds two
public `oihana\signals\Signal` properties to your model — one emitted *before*
the operation runs (so observers can inspect or veto the incoming data) and one
emitted *after* it completes (so observers can react to the result). When a
signal is emitted it carries a strongly-typed **notice** payload — a
`notices\Before*` or `notices\After*` object — that bundles the affected data,
the model that emitted the event (`target`) and an arbitrary `context` array.
These classes build directly on the external package
[`oihana/php-signals`](https://github.com/BcommeBois/oihana-php-signals)
(`Signal` for the emitter, `Payload` for the notice base class).

## Operations, signals & notices

Each operation maps to one signal trait (two `Signal` properties) and two notice
classes. Note that **truncate** notices carry no `data` (a truncate clears a
whole collection, there is no single affected document).

| Operation | Signal trait | Before signal / notice | After signal / notice |
|---|---|---|---|
| Insert | `HasInsertSignals` | `$beforeInsert` → `notices\BeforeInsert` | `$afterInsert` → `notices\AfterInsert` |
| Update | `HasUpdateSignals` | `$beforeUpdate` → `notices\BeforeUpdate` | `$afterUpdate` → `notices\AfterUpdate` |
| Delete | `HasDeleteSignals` | `$beforeDelete` → `notices\BeforeDelete` | `$afterDelete` → `notices\AfterDelete` |
| Replace | `HasReplaceSignals` | `$beforeReplace` → `notices\BeforeReplace` | `$afterReplace` → `notices\AfterReplace` |
| Upsert | `HasUpsertSignals` | `$beforeUpsert` → `notices\BeforeUpsert` | `$afterUpsert` → `notices\AfterUpsert` |
| Truncate | `HasTruncateSignals` | `$beforeTruncate` → `notices\BeforeTruncate` | `$afterTruncate` → `notices\AfterTruncate` |

The string discriminator carried by each notice (`type`) comes from the
`oihana\models\enums\NoticeType` enumeration, e.g. `NoticeType::BEFORE_INSERT`
(`'beforeInsert'`) or `NoticeType::AFTER_DELETE` (`'afterDelete'`).

## Adding signals to a model

A `Has*Signals` trait declares its two signal properties as `null` and provides
two helpers:

- `initialize*Signals()` — creates the two `Signal` instances (chainable).
- `release*Signals()` — disconnects and nullifies both signals (chainable).

Because the properties start out `null`, always call the `initialize*` helper
before connecting or emitting, and guard accesses with the `?->` operator.

```php
use oihana\models\traits\signals\HasInsertSignals;

class DocumentModel
{
    use HasInsertSignals;
}

$model = new DocumentModel();
$model->initializeInsertSignals(); // creates $beforeInsert and $afterInsert
```

## Connecting a listener and reading the notice

A listener is any callable connected to a signal via `connect()`. When the model
emits a notice, the listener receives that `Payload` object and can read its
public properties: `data`, `target`, `context` and `type`.

```php
use oihana\models\notices\AfterInsert;

$model->initializeInsertSignals();

$model->afterInsert?->connect( function( AfterInsert $notice )
{
    // $notice->data    : the inserted document(s) / result
    // $notice->target  : the model that emitted the signal
    // $notice->context : the contextual array passed at emit time
    // $notice->type    : NoticeType::AFTER_INSERT ('afterInsert')

    echo 'Inserted: ' . json_encode( $notice->data ) . PHP_EOL;
} );
```

## Emitting a notice

A model emits a notice by building the matching `Before*` / `After*` payload and
passing it to `Signal::emit()`. The `before` hook runs first (typically with the
incoming `$document`), the operation is performed, then the `after` hook runs
with the result.

```php
use oihana\models\notices\BeforeInsert;
use oihana\models\notices\AfterInsert;

// before the write
$this->beforeInsert?->emit( new BeforeInsert(
    data    : $document,
    target  : $this,
    context : [ 'collection' => 'users' ]
) );

$result = /* ... perform the actual insertion ... */ ;

// after the write
$this->afterInsert?->emit( new AfterInsert(
    data    : $result,
    target  : $this,
    context : [ 'collection' => 'users' ]
) );
```

All document notices share the same constructor signature
(`data`, `target`, `context`); the two **truncate** notices omit `data` and
accept only `target` and `context`:

```php
use oihana\models\notices\BeforeTruncate;

$this->beforeTruncate?->emit( new BeforeTruncate(
    target  : $this,
    context : [ 'collection' => 'sessions' ]
) );
```

## Priorities, one-shot listeners and cleanup

`Signal::connect()` accepts a `priority` (higher runs first) and an
`autoDisconnect` flag (the listener is removed after its first call). When you
are done with a model, release its signals to disconnect every listener:

```php
$model->beforeInsert?->connect(
    fn( $notice ) => audit( $notice ),
    priority       : 100,   // runs before lower-priority listeners
    autoDisconnect : false
);

// later, tear everything down
$model->releaseInsertSignals(); // disconnects + nullifies both signals
```

## Next steps

- [Documents](documents.md) — the model classes that emit these signals.
- [Enumerations](enums.md) — `NoticeType` and the other model enums.
- [Tests & coverage](testing.md) — how the signal/notice behaviour is verified.
- External: [`oihana/php-signals`](https://github.com/BcommeBois/oihana-php-signals)
  — the underlying `Signal` and `Payload` primitives.
