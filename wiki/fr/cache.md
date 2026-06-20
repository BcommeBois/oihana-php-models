# Cache

`oihana\models\traits\CacheableTrait` dote n'importe quelle classe d'une couche
de cache PSR-16 (`Psr\SimpleCache\CacheInterface`) standardisée : une instance de
cache, un interrupteur d'activation, un TTL par défaut, ainsi que les aides pour
lire, écrire et vider les entrées. C'est le socle des collections de modèles
mises en cache, généralement adossées à [Scrapbook](https://www.scrapbook.cash)
et câblées via l'aide [`cacheCollection()`](helpers.md).

## `CacheableTrait`

Le trait expose trois propriétés publiques et les opérations qui agissent sur
elles. Aucune méthode ne fait quoi que ce soit lorsque `$cache` vaut `null` :
une classe peut donc utiliser le trait en toute sécurité, même avant qu'un
backend ne soit câblé.

### Propriétés & constantes

| Membre | Type | Défaut | Description |
|---|---|---|---|
| `$cache` | `?CacheInterface` | `null` | Le store PSR-16. Lorsqu'il vaut `null`, chaque opération est sans effet. |
| `$cacheable` | `bool` | `true` | Interrupteur principal. Les écritures via `setCache()` / `setCacheMultiple()` sont ignorées quand il vaut `false`. |
| `$ttl` | `null\|int\|DateInterval` | `null` | Expiration par défaut appliquée lorsqu'un appel n'indique pas son propre TTL. |
| `CacheableTrait::CACHE` | `string` | `'cache'` | Clé du tableau d'init pour l'instance de cache ou son identifiant de conteneur. |
| `CacheableTrait::CACHEABLE` | `string` | `'cacheable'` | Clé du tableau d'init pour l'interrupteur `$cacheable`. |
| `CacheableTrait::TTL` | `string` | `'ttl'` | Clé du tableau d'init pour le TTL par défaut. |

### Opérations

| Méthode | Retourne | Description |
|---|---|---|
| `getCache( string $key )` | `mixed` | Valeur stockée sous `$key`, ou `null`. |
| `hasCache( ?string $key )` | `bool` | `true` si `$key` est une chaîne et présente dans le store. |
| `setCache( string $key , mixed $value , null\|int\|DateInterval $ttl = null )` | `bool` | Persiste une entrée. Retourne `false` quand `$cacheable` vaut `false`. Utilise `$this->ttl` si `$ttl` est omis. |
| `setCacheMultiple( array $values , null\|int\|DateInterval $ttl = null )` | `bool` | Persiste plusieurs paires `clé => valeur`. Retourne `false` quand `$cacheable` vaut `false`. |
| `deleteCache( string $key )` | `void` | Supprime une seule entrée. |
| `clearCache()` | `void` | Vide l'intégralité du store. |
| `isCacheable( array $init = [] )` | `bool` | `true` lorsqu'un `$cache` est défini **et** que le cache est activé (un override `$init['cacheable']` prime sur la propriété). |

### Aides d'initialisation

Ces méthodes fluides hydratent la configuration depuis un tableau `$init` et
retournent `$this` (ou `static`), ce qui permet de les chaîner — typiquement
depuis un constructeur.

| Méthode | Description |
|---|---|
| `initializeCache( array $init = [] , ?Container $container = null )` | Résout `$cache` depuis `$init['cache']`. Lorsque la valeur est une chaîne et qu'un `Container` PSR-11 est fourni, elle est récupérée depuis le conteneur. Puis exécute `initializeCacheable()` et `initializeTtl()`. |
| `initializeCacheable( array $init = [] )` | Définit `$cacheable` depuis `$init['cacheable']` (conserve la valeur courante si absent). |
| `initializeTtl( array $init = [] )` | Définit `$ttl` depuis `$init['ttl']` (conserve la valeur courante si absent). |

## Câbler un cache PSR-16

N'importe quelle `Psr\SimpleCache\CacheInterface` convient. L'exemple ci-dessous
construit un store Scrapbook au-dessus de Memcached et l'enveloppe dans
l'adaptateur PSR-16 :

```php
use Memcached;
use MatthiasMullie\Scrapbook\Adapters\Memcached as ScrapbookMemcached;
use MatthiasMullie\Scrapbook\Psr16\SimpleCache;

$client = new Memcached() ;
$client->addServer( '127.0.0.1' , 11211 ) ;

$cache = new SimpleCache( new ScrapbookMemcached( $client ) ) ;
```

## Activer le cache sur un modèle

Ajoutez le trait à votre classe, puis hydratez-le depuis un tableau `$init`.
Lorsque la clé `cache` est un identifiant de conteneur, passez le conteneur DI
pour qu'il puisse être résolu :

```php
use DateInterval;
use oihana\models\traits\CacheableTrait;

class ProductModel
{
    use CacheableTrait;

    public function __construct( array $init = [] , ?\DI\Container $container = null )
    {
        $this->initializeCache( $init , $container ) ;
    }
}

$model = new ProductModel(
[
    ProductModel::CACHE     => $cache ,                   // CacheInterface ou identifiant de conteneur
    ProductModel::CACHEABLE => true ,
    ProductModel::TTL       => new DateInterval( 'PT1H' ) , // 1 heure par défaut
] ) ;
```

## Lire & écrire

Une fois câblé, utilisez directement les opérations du trait. Les écritures
respectent l'interrupteur principal et retombent sur le TTL par défaut :

```php
if ( ! $model->hasCache( 'products:42' ) )
{
    $model->setCache( 'products:42' , [ 'id' => 42 , 'name' => 'Widget' ] ) ;
}

$product = $model->getCache( 'products:42' ) ; // ['id' => 42, 'name' => 'Widget']

$model->deleteCache( 'products:42' ) ; // retire une entrée
$model->clearCache() ;                 // vide tout le store
```

Pour désactiver temporairement la persistance sans débrancher le cache, basculez
l'interrupteur — `setCache()` et `setCacheMultiple()` deviennent sans effet,
tandis que les lectures continuent de fonctionner :

```php
$model->cacheable = false ;
$model->setCache( 'products:42' , $value ) ; // retourne false, rien n'est stocké
```

## Étapes suivantes

- [Documents](documents.md) — les classes de modèle qui consomment cette couche de cache.
- [Helpers](helpers.md) — `cacheCollection()`, le constructeur de collection PSR-16 namespacée.
- [Tests & couverture](testing.md) — comment le comportement du cache est vérifié.
