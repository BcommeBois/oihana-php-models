# Introduction

`oihana/php-models` rassemble les briques de modèles documentaires qui vivaient auparavant dans `oihana/php-system`, extraites dans un paquet ciblé afin qu'un projet puisse dépendre de la couche modèle **sans** tirer une pile HTTP, un moteur de templates ou une couche de routage.

Elle offre une surface réduite et composable : un `Model` de base relié à un conteneur d'injection de dépendances, une couche CRUD documentaire unique, des modèles adossés à PDO, un pipeline déclaratif de transformation de propriétés, une mise en cache PSR-16 et des signaux de cycle de vie.

## Ce qu'elle fournit

| Composant | Type | Rôle |
|---|---|---|
| `Model` | classe | Modèle de base intégrant une instance PDO avec support du conteneur DI. |
| `pdo\PDOModel` | classe | Modèle adossé à PDO pour les sources relationnelles. |
| `pdo\PDOTrait` | trait | Opérations PDO (liaison, récupération) pour n'importe quelle classe. |
| `traits\DocumentsTrait` | trait | La couche CRUD documentaire (list, get, count, insert, update, upsert, replace, delete, truncate, exist, last, stream). |
| `traits\AlterDocumentTrait` | trait | Pipeline déclaratif de transformation de propriétés (piloté par l'énumération `Alter`). |
| `traits\CacheableTrait` | trait | Mise en cache de collections via PSR-16. |
| `traits\signals\Has*Signals` | traits | Signaux de cycle de vie du modèle (avant/après chaque verbe CRUD). |
| `notices\Before*` / `notices\After*` | classes | Charges utiles (payloads) transportées par les signaux de cycle de vie. |
| `traits\SchemaTrait` | trait | Compatibilité Schema.org au-dessus de `org\schema\Thing`. |
| `interfaces\*` | interfaces | Contrats CRUD par verbe + l'interface agrégatrice `DocumentsModel`. |
| `enums\Alter` / `ModelParam` / `NoticeType` | classes | Clés de configuration fortement typées (pas de *magic strings*). |

## La philosophie *oihana*

- **PHP 8.4+ uniquement** — constantes typées, *property hooks*, aucun *shim* hérité.
- **Pas de *magic strings*** — chaque clé de configuration est une constante typée dans une classe basée sur `ConstantsTrait` (`Alter`, `ModelParam`, `NoticeType`) ; le projet n'utilise jamais d'énumération native PHP.
- **Composable** — chaque trait a une responsabilité unique et se combine librement.
- **Testé** — 100 % de couverture de lignes, mode strict PHPUnit (voir [Tests & couverture](../testing.md)).

## Étapes suivantes

- [Installation](installation.md)
- [Dépendances](dependencies.md)
- [Modèles](../models.md)
