# Documents

La couche document est un ensemble de traits composables qui transforment une
classe en un modèle CRUD complet au-dessus d'un backend de stockage (ArangoDB,
SQL OpenEdge, en mémoire, …). Toutes les opérations partagent la même forme :
elles acceptent un unique tableau d'options `array $init = []` et renvoient un
type qui dépend de l'action. `DocumentsTrait` relie le modèle à un conteneur
d'injection de dépendances et ajoute des assertions d'existence, tandis que des
traits plus petits (`ConditionsTrait`, `ListModelTrait`, `EnsureKeysTrait`,
`BindsTrait`) fournissent les briques réutilisables que chaque modèle compose.

## Le contrat `DocumentsModel`

`oihana\models\interfaces\DocumentsModel` regroupe toutes les opérations de type
CRUD derrière un seul contrat. Chaque méthode prend un tableau d'options `$init`
optionnel, ce qui garde les appelants uniformes quel que soit le backend.

| Méthode | Renvoie | Rôle |
|---|---|---|
| `count( array $init = [] )` | `int` | Compter les documents correspondant aux critères. |
| `exist( array $init = [] )` | `bool` | Vérifier qu'un document existe pour les critères. |
| `get( array $init = [] )` | `mixed` | Récupérer un document ou une valeur unique. |
| `list( array $init = [] )` | `array` | Lister les documents selon les critères de filtrage / tri. |
| `last( array $init = [] )` | `mixed` | Obtenir le dernier document correspondant aux options. |
| `stream( array $init = [] )` | `Generator` | Itérer paresseusement sur les documents. |
| `insert( array $init = [] )` | `mixed` | Insérer un nouveau document. |
| `update( array $init = [] )` | `mixed` | Mettre à jour les champs d'un document existant. |
| `updateDate( array $init = [], string $property = Schema::MODIFIED )` | `mixed` | Horodater une propriété de date avec la date courante. |
| `replace( array $init = [] )` | `mixed` | Remplacer entièrement un document existant. |
| `upsert( array $init = [] )` | `mixed` | Insérer ou mettre à jour selon l'existence. |
| `delete( array $init = [] )` | `null\|array\|object` | Supprimer un ou plusieurs documents. |
| `truncate( array $init = [] )` | `mixed` | Retirer tous les documents du stockage. |

```php
use oihana\models\interfaces\DocumentsModel;

/** @var DocumentsModel $model */
$total = $model->count() ;
$user  = $model->get( [ 'binds' => [ 'id' => 42 ] ] ) ;
$page  = $model->list( [ 'sort' => 'name' ] ) ;
$model->upsert( [ 'value' => [ 'id' => 42 , 'status' => 'active' ] ] ) ;
$model->delete( [ 'binds' => [ 'id' => 42 ] ] ) ;
```

## `DocumentsTrait` — câblage du conteneur & assertions

`oihana\models\traits\DocumentsTrait` utilise `ContainerTrait`. Il résout un
`DocumentsModel` (instance ou identifiant de service DI) et garantit qu'un
document référencé existe réellement.

```php
use oihana\models\traits\DocumentsTrait;

class ProductService
{
    use DocumentsTrait ;
}
```

### `getDocumentsModel()`

Renvoie un `DocumentsModel`, le résolvant depuis le conteneur lorsqu'une chaîne
d'identifiant de service est passée. Renvoie `null` s'il ne peut être résolu en
`DocumentsModel`.

```php
$products = $service->getDocumentsModel( 'products.model' ) ; // depuis le conteneur
$products = $service->getDocumentsModel( $existingModel ) ;    // passe-plat
```

### `assertExistInModel()`

Garantit qu'un document existe dans un `ExistModel`, en levant `Error404` sinon.
Elle lit la clé de recherche (par défaut `id`) sur un objet ou accepte un id
scalaire, et délègue à `$model->exist( [ 'binds' => [ $key => $id ] ] )`.

```php
$service->assertExistInModel( $document , $model , 'product' ) ;        // clé 'id'
$service->assertExistInModel( 42 , $model , 'product' ) ;               // id scalaire
$service->assertExistInModel( $edge , $model , 'product' , '_key' ) ;   // clé personnalisée
```

## `ConditionsTrait` — règles de filtrage

Contient un tableau `$conditions` flexible (clauses WHERE / FILTER, contraintes
logiques, définitions propres au driver) et l'hydrate depuis l'option
`conditions`.

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

`initializeConditions()` renvoie `$this` pour le chaînage fluide ; une clé
absente réinitialise `$conditions` à un tableau vide.

## `ListModelTrait` — une référence `ListModel`

Ajoute une propriété optionnelle `$list` (`ListModel`), la résout depuis le
conteneur et contrôle sa présence.

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
  lit l'option `list` ; une chaîne est résolue via le conteneur.
- `assertListModel()` lève `UnexpectedValueException` lorsque `$list` n'est pas défini.

## `EnsureKeysTrait` — garantir des clés avec des valeurs par défaut

Garantit que certaines clés existent sur un document (ou sur chaque élément
d'une collection indexée), en remplissant les manquantes avec une valeur par
défaut. La configuration provient de l'option `ensure` ou de la propriété
d'instance `$ensure`.

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

Une forme abrégée `'ensure' => [ 'status' , 'tags' ]` est acceptée : seulement
les clés, `default` vaut `null` et `enforce` vaut `false`. `initializeEnsure()`
stocke la configuration sur l'instance pour réutilisation et renvoie `$this`.

## `BindsTrait` — paramètres de liaison PDO

Gère les valeurs de liaison par défaut utilisées dans les requêtes PDO et les
fusionne avec les liaisons fournies à l'exécution.

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

- `$binds` — la table de liaisons par défaut (par défaut `[]`).
- `BindsTrait::BINDS` — la constante de clé d'option `'binds'`.
- `initializeBinds()` écrase `$binds` depuis l'option `binds` (renvoie `$this`).
- `prepareBindVars()` renvoie les valeurs par défaut fusionnées avec les
  liaisons d'exécution (l'exécution l'emporte).

## Étapes suivantes

- [Models](models.md) — les classes de base de modèle qui assemblent ces traits.
- [PDO](pdo.md) — le modèle document adossé à SQL et la gestion de connexion.
- [Alters](alters.md) — les pipelines de transformation post-récupération.
- [Signals & notices](signals-notices.md) — observer les événements du cycle de vie du modèle.
- [Tests & coverage](testing.md) — lancer la suite et vérifier la couverture.
