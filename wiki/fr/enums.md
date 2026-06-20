# Énumérations

Les clés récurrentes, les noms d'opérations et les signaux de cycle de vie sont
exposés sous forme de **constantes typées** regroupées dans des classes
utilitaires, plutôt que de *magic strings* dispersées dans le code. Ces classes
ne sont *pas* des `enum` natifs PHP : chacune utilise
`oihana\reflect\traits\ConstantsTrait`, ce qui permet de garder des valeurs
`string` simples utilisables partout, tout en restant introspectables
(`::getConstants()`, `::getConstant()`, `::includes()`…).

## `Alter`

`oihana\models\enums\Alter` — le catalogue des opérations de transformation ou
de filtrage applicables à une propriété d'objet ou à une clé de tableau lors de
la normalisation des données dans les modèles ou les collections. Voir
[Alters](alters.md) pour savoir comment ces opérations sont déclarées et
exécutées.

| Constante | Valeur | Signification |
|---|---|---|
| `Alter::ARRAY` | `'array'` | Convertit / encapsule la valeur dans un tableau. |
| `Alter::CALL` | `'call'` | Applique un callback personnalisé à la valeur. |
| `Alter::CLEAN` | `'clean'` | Supprime les entrées vides / nulles de la valeur. |
| `Alter::FLOAT` | `'float'` | Convertit la valeur en flottant. |
| `Alter::GET` | `'get'` | Extrait une valeur via un getter. |
| `Alter::HYDRATE` | `'hydrate'` | Hydrate la valeur en objet typé. |
| `Alter::LIST` | `'list'` | Traite la valeur comme une liste. |
| `Alter::INT` | `'int'` | Convertit la valeur en entier. |
| `Alter::JSON_PARSE` | `'jsonParse'` | Décode une chaîne JSON en valeur. |
| `Alter::JSON_STRINGIFY` | `'jsonStringify'` | Encode la valeur en chaîne JSON. |
| `Alter::LISTIFY` | `'listify'` | Transforme la valeur en liste normalisée. |
| `Alter::MAP` | `'map'` | Applique une transformation à chaque élément de la valeur. |
| `Alter::NORMALIZE` | `'normalize'` | Normalise la valeur. |
| `Alter::NOT` | `'not'` | Nie / inverse la valeur. |
| `Alter::URL` | `'url'` | Construit ou normalise une URL à partir de la valeur. |
| `Alter::VALUE` | `'value'` | Remplace par une valeur fixe. |

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

`oihana\models\enums\ModelParam` — les clés acceptées par les constructeurs de
modèles et les tableaux de configuration. Les constantes sont définies dans
`oihana\models\enums\traits\ModelParamTrait` et exposées via la classe
`ModelParam` (qui inclut aussi `ConstantsTrait` pour l'introspection), afin que
le trait puisse être réutilisé par des énumérations de paramètres plus
spécialisées.

| Constante | Valeur | Signification |
|---|---|---|
| `ModelParam::ALTER_KEY` | `'alterKey'` | Identifiant par défaut de la clé `alter`. |
| `ModelParam::ALTERS` | `'alters'` | Les règles d'altération à appliquer. |
| `ModelParam::BINDS` | `'binds'` | Valeurs liées à une requête. |
| `ModelParam::BINDS_ALTERS` | `'bindsAlters'` | Altérations appliquées aux valeurs liées. |
| `ModelParam::CACHE` | `'cache'` | Le backend / la configuration de cache. |
| `ModelParam::CONDITIONS` | `'conditions'` | Conditions de requête. |
| `ModelParam::DEBUG` | `'debug'` | Active le comportement de débogage. |
| `ModelParam::DEFAULT` | `'default'` | Valeur par défaut. |
| `ModelParam::DEFER_ASSIGNMENT` | `'deferAssignment'` | Diffère l'affectation des propriétés. |
| `ModelParam::ENFORCE` | `'enforce'` | Impose une contrainte. |
| `ModelParam::ENSURE` | `'ensure'` | Garantit une valeur / un état. |
| `ModelParam::ID` | `'id'` | L'identifiant. |
| `ModelParam::KEY` | `'key'` | Une clé unique. |
| `ModelParam::KEYS` | `'keys'` | Un ensemble de clés. |
| `ModelParam::LIST` | `'list'` | Une valeur de type liste. |
| `ModelParam::LOGGABLE` | `'loggable'` | Le drapeau / l'objet loggable. |
| `ModelParam::LOGGER` | `'logger'` | L'instance de logger. |
| `ModelParam::MOCK` | `'mock'` | Données / mode mock. |
| `ModelParam::MODEL` | `'model'` | La définition du modèle. |
| `ModelParam::OPTIMIZED` | `'optimized'` | Drapeau du mode optimisé. |
| `ModelParam::OWNER` | `'owner'` | La référence du propriétaire. |
| `ModelParam::PDO` | `'pdo'` | La connexion PDO. |
| `ModelParam::QUERY` | `'query'` | La requête. |
| `ModelParam::QUERY_BUILDER` | `'queryBuilder'` | Le constructeur de requêtes. |
| `ModelParam::QUERY_FIELDS` | `'queryFields'` | Champs sélectionnés par la requête. |
| `ModelParam::QUERY_ID` | `'queryId'` | L'identifiant de la requête. |
| `ModelParam::SCHEMA` | `'schema'` | La définition du schéma. |
| `ModelParam::SORT` | `'sort'` | L'expression de tri. |
| `ModelParam::SORT_DEFAULT` | `'sortDefault'` | L'expression de tri par défaut. |
| `ModelParam::THROWABLE` | `'throwable'` | Indique s'il faut lever une exception en cas d'erreur. |
| `ModelParam::TTL` | `'ttl'` | La durée de vie du cache. |
| `ModelParam::VALUE` | `'value'` | Une valeur. |

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

`oihana\models\enums\NoticeType` — les signaux de cycle de vie émis autour des
mutations de modèle, utilisés pour diffuser les notifications `before*` /
`after*`. Voir [Signaux & notices](signals-notices.md) pour savoir comment les
écouteurs s'y abonnent.

| Constante | Valeur | Signification |
|---|---|---|
| `NoticeType::BEFORE_INSERT` | `'beforeInsert'` | Émis avant une insertion. |
| `NoticeType::AFTER_INSERT` | `'afterInsert'` | Émis après une insertion. |
| `NoticeType::BEFORE_UPDATE` | `'beforeUpdate'` | Émis avant une mise à jour. |
| `NoticeType::AFTER_UPDATE` | `'afterReplace'` | Émis après une mise à jour. |
| `NoticeType::BEFORE_REPLACE` | `'beforeReplace'` | Émis avant un remplacement. |
| `NoticeType::AFTER_REPLACE` | `'afterReplace'` | Émis après un remplacement. |
| `NoticeType::BEFORE_DELETE` | `'beforeDelete'` | Émis avant une suppression. |
| `NoticeType::AFTER_DELETE` | `'afterDelete'` | Émis après une suppression. |
| `NoticeType::BEFORE_UPSERT` | `'beforeUpsert'` | Émis avant un upsert. |
| `NoticeType::AFTER_UPSERT` | `'afterUpsert'` | Émis après un upsert. |
| `NoticeType::BEFORE_TRUNCATE` | `'beforeTruncate'` | Émis avant une troncature. |
| `NoticeType::AFTER_TRUNCATE` | `'afterTruncate'` | Émis après une troncature. |

> Note : `NoticeType::AFTER_UPDATE` vaut actuellement `'afterReplace'`, la même
> valeur que `NoticeType::AFTER_REPLACE` — les écouteurs qui distinguent les deux
> doivent s'appuyer sur les signaux `before*`.

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

## Étapes suivantes

- [Alters](alters.md)
- [Models](models.md)
- [Signaux & notices](signals-notices.md)
- [Tests & couverture](testing.md)
