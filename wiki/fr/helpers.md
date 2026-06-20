# Helpers

Quatre fonctions libres enregistrées via le `autoload.files` de composer, toutes
dans l'espace de noms `oihana\models\helpers`. Ce sont des fonctions globales,
pas des méthodes de classe : importez chacune avec une instruction
`use function`, p. ex. `use function oihana\models\helpers\getModel;`. Elles
couvrent la « glu » la plus courante pour câbler des modèles dans un conteneur
PSR-11 / d'injection de dépendances : résoudre un modèle, construire une URL de
document, et créer une collection de cache à espace de noms.

## `getModel` — résoudre un `Model` depuis un conteneur

Résout une instance `oihana\models\Model` à partir d'une définition flexible. La
définition peut être un `Model` (retourné tel quel), un tableau portant une clé
`ModelParam::MODEL`, un identifiant de service (chaîne) résolu depuis le
conteneur, ou `null`.

```php
public function getModel
(
    array|string|null|Model $definition = null ,
    ?ContainerInterface     $container  = null ,
    ?Model                  $default    = null
) : ?Model
```

- `$definition` — une instance `Model`, un tableau avec la clé
  `ModelParam::MODEL`, un identifiant (chaîne) recherché dans `$container`, ou
  `null`.
- `$container` — conteneur PSR-11 optionnel utilisé pour résoudre une définition
  de type chaîne.
- `$default` — repli optionnel retourné lorsque rien n'a pu être résolu.

**Retourne** le `Model` résolu, le `$default` fourni, ou `null`.

Lève `Psr\Container\ContainerExceptionInterface` /
`Psr\Container\NotFoundExceptionInterface` en cas d'erreur du conteneur.

```php
use function oihana\models\helpers\getModel;
use oihana\models\enums\ModelParam;

// Depuis un identifiant (chaîne) dans le conteneur
$model = getModel( 'mainModel' , $container ) ;

// Depuis une définition sous forme de tableau
$model = getModel( [ ModelParam::MODEL => 'mainModel' ] , $container ) ;

// Avec un repli lorsque rien n'est résolu
$model = getModel( 'unknown' , $container , $defaultModel ) ;
```

## `getDocumentsModel` — résoudre un `DocumentsModel` depuis un conteneur

Même schéma de résolution que `getModel`, spécialisé pour l'interface
`oihana\models\interfaces\DocumentsModel`. Une instance `DocumentsModel` est
retournée directement ; un identifiant (chaîne) est résolu depuis le conteneur ;
sinon, le repli est retourné.

```php
public function getDocumentsModel
(
    string|null|DocumentsModel $definition = null ,
    ?ContainerInterface        $container  = null ,
    ?DocumentsModel            $default    = null
) : ?DocumentsModel
```

- `$definition` — une instance `DocumentsModel`, un identifiant de service
  (chaîne), ou `null`.
- `$container` — conteneur PSR-11 optionnel utilisé pour résoudre une définition
  de type chaîne.
- `$default` — repli optionnel retourné lorsqu'aucune instance valide n'est
  trouvée.

**Retourne** le `DocumentsModel` résolu, le `$default` fourni, ou `null`.

Lève `Psr\Container\ContainerExceptionInterface` /
`Psr\Container\NotFoundExceptionInterface` en cas d'erreur du conteneur.

```php
use function oihana\models\helpers\getDocumentsModel;
use oihana\models\interfaces\DocumentsModel;

// Instance directe
$model = new MyDocumentsModel() ;
echo getDocumentsModel( $model ) === $model ? 'ok' : 'fail' ; // ok

// Identifiant (chaîne) résolu via le conteneur
$resolved = getDocumentsModel( 'mainModel' , $container ) ;
echo $resolved instanceof DocumentsModel ? 'ok' : 'fail' ;    // ok

// Repli vers un modèle par défaut
$default = new DefaultDocumentsModel() ;
echo getDocumentsModel( 'unknown' , $container , $default ) === $default ? 'ok' : 'fail' ; // ok
```

## `documentUrl` — construire une URL de document depuis l'URL de base

Génère une URL de document complète en joignant une URL de base (lue depuis le
conteneur) avec un chemin relatif. Couramment utilisée dans les définitions IoC
des modèles pour exposer l'URL accessible d'un document ou d'une ressource.

```php
public function documentUrl
(
    string              $path          = Char::EMPTY ,
    ?ContainerInterface $container     = null ,
    ?string             $definition    = 'baseUrl' ,
    bool                $trailingSlash = false
) : string
```

- `$path` — chemin relatif du document (défaut : chaîne vide).
- `$container` — conteneur DI optionnel depuis lequel récupérer l'URL de base.
- `$definition` — clé utilisée pour récupérer l'URL de base dans le conteneur
  (défaut : `'baseUrl'`).
- `$trailingSlash` — indique s'il faut ajouter une barre oblique finale au
  résultat (défaut : `false`).

**Retourne** l'URL de document entièrement résolue, sous forme de chaîne.

Lève `Psr\Container\ContainerExceptionInterface` /
`Psr\Container\NotFoundExceptionInterface` en cas d'erreur du conteneur.

```php
use function oihana\models\helpers\documentUrl;

$url = documentUrl( 'uploads/image.png' , $container ) ;
// p. ex. 'https://example.com/uploads/image.png'

$urlWithSlash = documentUrl( 'uploads' , $container , 'baseUrl' , true ) ;
// 'https://example.com/uploads/'
```

## `cacheCollection` — créer un cache PSR-16 à espace de noms

Crée une collection de cache isolée, à espace de noms, à partir d'un magasin
clé/valeur enregistré dans le conteneur DI. Elle récupère un
`MatthiasMullie\Scrapbook\KeyValueStore`, prend la collection demandée, et
l'enveloppe dans un `MatthiasMullie\Scrapbook\Psr16\SimpleCache` PSR-16. Cela
permet de conserver plusieurs caches logiques (par fonctionnalité, domaine ou
module) au sein d'un même backend.

```php
public function cacheCollection
(
    Container $container  ,
    string    $collection ,
    string    $definition
) : ?SimpleCache
```

- `$container` — le `DI\Container` PHP-DI utilisé pour résoudre le magasin de
  cache.
- `$collection` — le nom de collection (espace de noms) à créer dans le magasin.
- `$definition` — l'identifiant d'entrée du conteneur du magasin clé/valeur de
  base.

**Retourne** un `SimpleCache` PSR-16 limité à la collection, ou `null` si la
définition est introuvable ou n'est pas un `KeyValueStore`.

Lève `DI\DependencyException` / `DI\NotFoundException` en cas d'erreur du
conteneur.

```php
use function oihana\models\helpers\cacheCollection;

// Récupérer une collection de cache nommée « users »
$userCache = cacheCollection( $container , 'users' , 'cache:memory' ) ;

// Stocker et récupérer des valeurs
$userCache->set( 'id:42' , [ 'name' => 'Alice' ] ) ;
$data = $userCache->get( 'id:42' ) ;
```

## Étapes suivantes

- [Models](models.md) — le `Model` de base résolu par `getModel`.
- [Documents](documents.md) — le `DocumentsModel` résolu par `getDocumentsModel`.
- [Cache](cache.md) — la couche de cache utilisée avec `cacheCollection`.
- [Tests & couverture](testing.md) — lancer et étendre la suite de tests des helpers.
