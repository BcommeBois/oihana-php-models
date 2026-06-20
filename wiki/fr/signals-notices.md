# Signals & notices

Chaque opération du cycle de vie d'un modèle (insert, update, delete, replace,
truncate, upsert) expose une paire de crochets **avant/après**. Un trait
`Has*Signals` ajoute à votre modèle deux propriétés publiques
`oihana\signals\Signal` — l'une émise *avant* l'exécution de l'opération (afin
que les observateurs puissent inspecter ou refuser les données entrantes) et
l'autre émise *après* son achèvement (afin que les observateurs puissent réagir
au résultat). Lorsqu'un signal est émis, il transporte une **notice** fortement
typée — un objet `notices\Before*` ou `notices\After*` — qui regroupe les
données concernées, le modèle qui a émis l'événement (`target`) et un tableau
de `context` arbitraire. Ces classes reposent directement sur le paquet externe
[`oihana/php-signals`](https://github.com/BcommeBois/oihana-php-signals)
(`Signal` pour l'émetteur, `Payload` pour la classe de base des notices).

## Opérations, signals & notices

Chaque opération correspond à un trait de signal (deux propriétés `Signal`) et à
deux classes de notice. À noter que les notices **truncate** ne portent aucune
`data` (un truncate vide une collection entière, il n'y a pas de document unique
concerné).

| Opération | Trait de signal | Signal / notice avant | Signal / notice après |
|---|---|---|---|
| Insert | `HasInsertSignals` | `$beforeInsert` → `notices\BeforeInsert` | `$afterInsert` → `notices\AfterInsert` |
| Update | `HasUpdateSignals` | `$beforeUpdate` → `notices\BeforeUpdate` | `$afterUpdate` → `notices\AfterUpdate` |
| Delete | `HasDeleteSignals` | `$beforeDelete` → `notices\BeforeDelete` | `$afterDelete` → `notices\AfterDelete` |
| Replace | `HasReplaceSignals` | `$beforeReplace` → `notices\BeforeReplace` | `$afterReplace` → `notices\AfterReplace` |
| Upsert | `HasUpsertSignals` | `$beforeUpsert` → `notices\BeforeUpsert` | `$afterUpsert` → `notices\AfterUpsert` |
| Truncate | `HasTruncateSignals` | `$beforeTruncate` → `notices\BeforeTruncate` | `$afterTruncate` → `notices\AfterTruncate` |

Le discriminant textuel porté par chaque notice (`type`) provient de
l'énumération `oihana\models\enums\NoticeType`, p. ex. `NoticeType::BEFORE_INSERT`
(`'beforeInsert'`) ou `NoticeType::AFTER_DELETE` (`'afterDelete'`).

## Ajouter des signals à un modèle

Un trait `Has*Signals` déclare ses deux propriétés de signal à `null` et fournit
deux aides :

- `initialize*Signals()` — crée les deux instances `Signal` (chaînable).
- `release*Signals()` — déconnecte et annule les deux signals (chaînable).

Comme les propriétés démarrent à `null`, appelez toujours l'aide `initialize*`
avant de connecter ou d'émettre, et protégez les accès avec l'opérateur `?->`.

```php
use oihana\models\traits\signals\HasInsertSignals;

class DocumentModel
{
    use HasInsertSignals;
}

$model = new DocumentModel();
$model->initializeInsertSignals(); // crée $beforeInsert et $afterInsert
```

## Connecter un écouteur et lire la notice

Un écouteur est n'importe quel callable connecté à un signal via `connect()`.
Lorsque le modèle émet une notice, l'écouteur reçoit cet objet `Payload` et peut
lire ses propriétés publiques : `data`, `target`, `context` et `type`.

```php
use oihana\models\notices\AfterInsert;

$model->initializeInsertSignals();

$model->afterInsert?->connect( function( AfterInsert $notice )
{
    // $notice->data    : le ou les documents insérés / le résultat
    // $notice->target  : le modèle qui a émis le signal
    // $notice->context : le tableau contextuel passé au moment de l'émission
    // $notice->type    : NoticeType::AFTER_INSERT ('afterInsert')

    echo 'Inséré : ' . json_encode( $notice->data ) . PHP_EOL;
} );
```

## Émettre une notice

Un modèle émet une notice en construisant la charge `Before*` / `After*`
correspondante et en la passant à `Signal::emit()`. Le crochet `before`
s'exécute d'abord (généralement avec le `$document` entrant), l'opération est
réalisée, puis le crochet `after` s'exécute avec le résultat.

```php
use oihana\models\notices\BeforeInsert;
use oihana\models\notices\AfterInsert;

// avant l'écriture
$this->beforeInsert?->emit( new BeforeInsert(
    data    : $document,
    target  : $this,
    context : [ 'collection' => 'users' ]
) );

$result = /* ... réaliser l'insertion effective ... */ ;

// après l'écriture
$this->afterInsert?->emit( new AfterInsert(
    data    : $result,
    target  : $this,
    context : [ 'collection' => 'users' ]
) );
```

Toutes les notices de document partagent la même signature de constructeur
(`data`, `target`, `context`) ; les deux notices **truncate** omettent `data`
et n'acceptent que `target` et `context` :

```php
use oihana\models\notices\BeforeTruncate;

$this->beforeTruncate?->emit( new BeforeTruncate(
    target  : $this,
    context : [ 'collection' => 'sessions' ]
) );
```

## Priorités, écouteurs uniques et nettoyage

`Signal::connect()` accepte une `priority` (la plus élevée s'exécute en premier)
et un indicateur `autoDisconnect` (l'écouteur est retiré après son premier
appel). Lorsque vous en avez terminé avec un modèle, libérez ses signals pour
déconnecter tous les écouteurs :

```php
$model->beforeInsert?->connect(
    fn( $notice ) => audit( $notice ),
    priority       : 100,   // s'exécute avant les écouteurs de priorité inférieure
    autoDisconnect : false
);

// plus tard, tout démonter
$model->releaseInsertSignals(); // déconnecte + annule les deux signals
```

## Étapes suivantes

- [Documents](documents.md) — les classes de modèle qui émettent ces signals.
- [Enumerations](enums.md) — `NoticeType` et les autres énumérations de modèle.
- [Tests & couverture](testing.md) — comment le comportement signal/notice est vérifié.
- Externe : [`oihana/php-signals`](https://github.com/BcommeBois/oihana-php-signals)
  — les primitives `Signal` et `Payload` sous-jacentes.
