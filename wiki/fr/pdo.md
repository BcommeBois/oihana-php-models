# PDO

`oihana\models\pdo\PDOModel` est un modèle de base pour les sources de données
relationnelles accessibles via une connexion
[PDO](https://www.php.net/manual/fr/book.pdo.php). Il encapsule une instance
`PDO`, prépare et exécute des requêtes paramétrées, lie les valeurs nommées de
façon sûre, et mappe chaque ligne vers un objet simple ou vers une classe de
schéma typée. Toute la logique de requête réside dans le trait réutilisable
`oihana\models\pdo\PDOTrait`, que vous pouvez intégrer à n'importe quelle classe.

## `PDOModel` — un modèle prêt à l'emploi

`PDOModel` étend [`Model`](models.md) et y ajoute la couche PDO. Il se construit à
partir d'un conteneur d'injection de dépendances et d'un tableau de configuration
optionnel :

```php
use DI\Container;
use oihana\models\pdo\PDOModel;

$container = new Container() ;

$config =
[
    'deferAssignment' => true ,
    'pdo'             => 'my_pdo_service' , // une instance PDO ou un id de service du conteneur
    'schema'          => MyEntity::class ,  // classe de schéma optionnelle pour le mappage
];

$model = new PDOModel( $container , $config ) ;

// Récupérer un seul enregistrement (paramètres nommés, liés)
$user = $model->fetch( 'SELECT * FROM users WHERE id = :id' , [ 'id' => 123 ] ) ;

// Récupérer tous les enregistrements correspondants
$users = $model->fetchAll( 'SELECT * FROM users WHERE active = :active' , [ 'active' => 1 ] ) ;
```

### Constructeur

```php
public function __construct( Container $container , array $init = [] )
```

Le tableau `$init` accepte les clés optionnelles suivantes (reflétées par les
constantes de l'enum `ModelParam`) :

| Clé | Constante | Rôle |
|---|---|---|
| `alters` | `ModelParam::ALTERS` | Altérations appliquées à chaque ligne récupérée via `AlterDocumentTrait`. |
| `binds` | `ModelParam::BINDS` | Liaisons par défaut utilisées par les traits de requête. |
| `deferAssignment` | `ModelParam::DEFER_ASSIGNMENT` | Si `true` (et qu'un schéma est défini), utilise `FETCH_PROPS_LATE` afin que le constructeur s'exécute avant l'affectation des propriétés. |
| `schema` | `ModelParam::SCHEMA` | Nom de classe pleinement qualifié utilisé comme cible de mappage `FETCH_CLASS`. |
| `pdo` | `ModelParam::PDO` | Une instance `PDO`, ou un id de service du conteneur qui en résout une. |

La valeur `pdo` est résolue par `initializePDO()` : une chaîne est recherchée dans
le conteneur (`$container->has(...)` puis `get(...)`) ; tout ce qui n'est pas un
`PDO` devient `null`.

## Méthodes de requête

Ces méthodes proviennent de `PDOTrait`. Chacune accepte des paramètres nommés et
liés ainsi qu'un dernier indicateur `bool $throwable` — lorsqu'il vaut `false`
(défaut), les exceptions sont journalisées et une valeur neutre est renvoyée ;
lorsqu'il vaut `true`, l'exception est relancée.

| Méthode | Renvoie | Rôle |
|---|---|---|
| `fetch( $query , $bindVars = [] , $throwable = false )` | `mixed\|null` | Exécute un SELECT et renvoie la première ligne (objet ou instance de schéma), ou `null`. |
| `fetchAll( $query , $bindVars = [] , $throwable = false )` | `array` | Renvoie toutes les lignes correspondantes ; tableau vide si aucune ou en cas d'échec. |
| `fetchAllAsGenerator( $query , $bindVars = [] , $throwable = false )` | `Generator<object>` | Diffuse les lignes une à une — économe en mémoire pour de gros jeux de résultats. |
| `fetchColumn( $query , $bindVars = [] , $column = 0 , $throwable = false )` | `mixed` | Renvoie une colonne (index base 0) de la première ligne, ou `null`. |
| `fetchColumnArray( $query , $bindVars = [] , $throwable = false )` | `array<int,string>` | Renvoie une liste plate construite à partir de la première colonne de chaque ligne. |

Aides complémentaires :

| Méthode | Renvoie | Rôle |
|---|---|---|
| `bindValues( $statement , $bindVars = [] )` | `void` | Lie les paramètres nommés à une requête préparée. |
| `initializeDefaultFetchMode( $statement )` | `void` | Applique `FETCH_ASSOC`, ou `FETCH_CLASS` (`+ FETCH_PROPS_LATE`) quand un schéma est défini. |
| `initializePDO( $init , $container = null )` | `static` | Résout et stocke l'instance `PDO`. |
| `initializeDeferAssignment( $init = [] )` | `static` | Lit l'indicateur `deferAssignment` depuis le tableau d'init. |
| `isConnected()` | `bool` | Indique si la connexion `PDO` sous-jacente est active. |

### Liaison des valeurs

`bindVars` est un tableau associatif. Un scalaire est lié tel quel ; un tableau à
deux éléments permet de passer un type PDO explicite :

```php
use PDO;

$rows = $model->fetchAll(
    'SELECT * FROM orders WHERE customer_id = :customer AND total >= :total' ,
    [
        'customer' => [ 42 , PDO::PARAM_INT ] , // valeur + type explicite
        'total'    => 100.0 ,                   // lié tel quel
    ]
) ;
```

Le `:` initial est ajouté pour vous — passez `customer`, et non `:customer`.

### Diffusion de gros jeux de résultats

`fetchAllAsGenerator()` produit un objet altéré à la fois et ferme le curseur en
fin d'itération, afin qu'un export d'un million de lignes ne soit jamais
matérialisé en mémoire :

```php
foreach ( $model->fetchAllAsGenerator( 'SELECT * FROM events ORDER BY id' ) as $event )
{
    process( $event ) ;
}
```

### Requêtes mono-colonne

```php
$count  = $model->fetchColumn( 'SELECT COUNT(*) FROM users' ) ;        // scalaire
$emails = $model->fetchColumnArray( 'SELECT email FROM users' ) ;       // ['a@x', 'b@y', ...]
```

### Mappage de schéma

Lorsque `schema` est une classe réelle, les lignes sont hydratées en instances de
celle-ci via `FETCH_CLASS`. Mettez `deferAssignment` à `true` pour ajouter
`FETCH_PROPS_LATE`, qui exécute le constructeur de la classe avant l'affectation
des valeurs de colonnes — utile lorsque votre constructeur pose des valeurs par
défaut que les colonnes doivent écraser. Sans schéma, les lignes sont renvoyées en
objets `stdClass` simples (`fetch`) ou en tableaux associatifs (`fetchAll`).

### Gestion des erreurs

Par défaut, une requête en échec est interceptée : en CLI l'échec est affiché
(requête, liaisons, message), sinon il est journalisé via l'aide `warning()` du
modèle, puis une valeur neutre est renvoyée (`null` / `[]`). Passez
`$throwable = true` pour faire remonter l'exception jusqu'à votre propre
gestionnaire.

## Étapes suivantes

- [Models](models.md) — la classe de base `Model` qu'étend `PDOModel`.
- [Documents](documents.md) — modèles orientés documents pour sources NoSQL.
- [Tests & couverture](testing.md) — comment la couche modèle est testée.
