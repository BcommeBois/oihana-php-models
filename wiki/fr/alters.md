# Alters

Les alters constituent un pipeline de transformation déclaratif, propriété par propriété. Plutôt que d'écrire du code impératif de conversion et de nettoyage, vous décrivez — dans un simple tableau associatif — quelle(s) transformation(s) appliquer à chaque clé d'un document (tableau ou objet). Le pipeline lit chaque propriété, exécute le ou les types `Alter` configurés sur sa valeur, puis réécrit le résultat. Une définition peut être un seul alter, un alter accompagné de paramètres, ou plusieurs alters chaînés de sorte que la sortie de l'un devienne l'entrée du suivant.

Le pipeline est fourni par trois traits, chacun exposant un point d'entrée différent mais partageant tous le même moteur d'alteration sous-jacent (`AlterTrait`) :

- `AlterDocumentTrait` — expose `$alters` et `alter( $document )` pour transformer un document complet, une liste de documents ou un objet.
- `AlterBindVarsTrait` — expose `$bindAlters` et `alterBindVars( $bindVars, $context )` pour transformer un tableau de variables liées (scopé par contexte), puis le nettoyer.
- `AlterTrait` — le moteur lui-même (`alterProperty()`, détection du chaînage, dispatch), composé d'un trait dédié par type d'alter sous `traits/alters/`.

Deux traits compagnons complètent le système : `AlterKeyTrait` fournit la clé de propriété par défaut (`$alterKey`, valant `Schema::ID`) utilisée par la génération d'URL, et `AlterValueTrait` implémente le remplacement par une valeur fixe.

## Types d'alter

Chaque type d'alter est une constante de l'énumération `oihana\models\enums\Alter`. Le tableau ci-dessous associe chaque type à son comportement et à la forme de sa définition.

| Type d'alter | Forme de la définition | Rôle |
|---|---|---|
| `Alter::ARRAY` | `[ Alter::ARRAY , ...sousAlters ]` | Découpe une chaîne séparée par `;` en tableau, puis applique les sous-alters listés à ses éléments (`CALL`, `CLEAN`, `FLOAT`, `GET`, `HYDRATE`, `INT`, `JSON_PARSE`, `NORMALIZE`, `NOT`). |
| `Alter::CALL` | `[ Alter::CALL , $callable , ...$args ]` | Invoque un callable comme `fn( $value , ...$args )` et remplace la valeur par son retour. Les chaînes sont résolues via `resolveCallable()`. |
| `Alter::CLEAN` | `Alter::CLEAN` | Supprime les éléments vides (`""`) et non définis d'un tableau. |
| `Alter::FLOAT` | `Alter::FLOAT` | Convertit la valeur en `float`, ou chaque élément en `float` s'il s'agit d'un tableau. |
| `Alter::GET` | `[ Alter::GET , $modelId , $key ]` | Remplace un identifiant par un document complet récupéré via un modèle Documents résolu depuis le conteneur (retourne `null` en cas d'échec). |
| `Alter::HYDRATE` | `[ Alter::HYDRATE , Class::class , $normalize?, $flags? ]` | Normalise (optionnel) puis hydrate une valeur tableau en instance de la classe donnée (les sous-classes de `Thing` utilisent leur constructeur, les autres la réflexion). Les tableaux vides deviennent `null`. |
| `Alter::INT` | `Alter::INT` | Convertit la valeur en `int`, ou chaque élément en `int` s'il s'agit d'un tableau. |
| `Alter::JSON_PARSE` | `[ Alter::JSON_PARSE , ...$argsJsonDecode ]` | Décode une chaîne JSON valide avec `json_decode()` ; les chaînes non-JSON sont laissées intactes. |
| `Alter::JSON_STRINGIFY` | `[ Alter::JSON_STRINGIFY , ...$argsJsonEncode ]` | Encode la valeur en chaîne JSON avec `json_encode()`. |
| `Alter::LISTIFY` | `[ Alter::LISTIFY , $separateur?, $remplacement?, $defaut? ]` | Découpe une chaîne/un tableau, élague et supprime les vides, puis recompose (défauts : découpe sur `;`, jointure avec `PHP_EOL`) ; retombe sur `$defaut` si le résultat est vide. |
| `Alter::MAP` | `[ Alter::MAP , $callable , ...$args ]` | Appelle `fn( &$document , $container , $key , $value , $params )` — a accès au document entier, et peut donc calculer une valeur à partir des propriétés voisines. |
| `Alter::NORMALIZE` | `[ Alter::NORMALIZE , $flags? ]` | Normalise la valeur avec `normalize()` (défaut `CleanFlag::DEFAULT \| CleanFlag::RETURN_NULL`) : élague, supprime vides/nulls récursivement. |
| `Alter::NOT` | `Alter::NOT` | Inverse un booléen (ou chaque élément d'un tableau de booléens). |
| `Alter::URL` | `[ Alter::URL , $path , $propriete?, $cleConteneur?, $slashFinal? ]` | Construit une URL en joignant une URL de base (optionnellement résolue depuis le conteneur), un segment de chemin et la valeur d'une propriété du document. |
| `Alter::VALUE` | `[ Alter::VALUE , $nouvelleValeur ]` | Remplace la propriété par une valeur fixe. |

> Note : l'énumération déclare aussi `Alter::LIST`, réservée et sans gestionnaire dédié pour l'instant (les types d'alter inconnus laissent la valeur inchangée).

## Transformer un document

Déclarez les règles dans `$alters`, puis appelez `alter()` :

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

Lorsque le document est un tableau séquentiel (une liste), `alter()` s'applique récursivement à chaque élément : la même définition `$alters` fonctionne donc pour un document unique comme pour une collection.

## Chaîner les alterations

La valeur d'une propriété peut être `[ Alter::A , Alter::B , ... ]` pour exécuter plusieurs alters en séquence, ou `[ Alter::A , [ Alter::B , ...args ] ]` pour chaîner des alters prenant des paramètres. Chaque étape reçoit la sortie de la précédente.

```php
$this->alters =
[
    // découpe, puis convertit chaque élément en float
    'prices' => [ Alter::ARRAY , Alter::FLOAT ],

    // normalise, puis hydrate le tableau nettoyé en objet
    'geo'    => [ Alter::NORMALIZE , [ Alter::HYDRATE , GeoCoordinates::class ] ],

    // valeur calculée depuis les propriétés voisines
    'total'  => [ Alter::MAP , fn( &$doc, $c, $k, $v, $p ) => $doc['price'] * ( 1 + ( $doc['vat'] ?? 0 ) ) ],
];
```

## Transformer les variables liées

`AlterBindVarsTrait` applique le même moteur à un tableau de variables liées, scopé par une clé de contexte, et exécute `clean()` sur le résultat :

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

## Étapes suivantes

- [Documents](documents.md) — la couche modèle qui consomme les alters en lecture/écriture.
- [Models](models.md) — les modèles de base composant les traits d'alteration.
- [Énumérations](enums.md) — l'énumération `Alter` et les clés de paramètres associées.
- [Tests & couverture](testing.md) — exécuter la suite de tests.
