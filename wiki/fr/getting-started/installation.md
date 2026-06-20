# Installation

## Prérequis

- **PHP 8.4 ou supérieur.**
- **[Composer](https://getcomposer.org/).**

La bibliothèque elle-même ne requiert aucune extension PHP particulière. Les
dépendances transitives peuvent nécessiter des extensions courantes (par
exemple `oihana/php-files` utilise `ext-fileinfo`), présentes dans la plupart
des distributions PHP. Un pilote PDO n'est nécessaire que si vous utilisez les
modèles adossés à PDO.

## Installation via Composer

```bash
composer require oihana/php-models
```

## Autochargement

Le paquet est autochargé via PSR-4, et ses quatre fonctions libres via
`autoload.files` de composer :

```json
{
    "autoload": {
        "psr-4": {
            "oihana\\models\\": "src/oihana/models"
        },
        "files": [
            "src/oihana/models/helpers/cacheCollection.php",
            "src/oihana/models/helpers/documentUrl.php",
            "src/oihana/models/helpers/getDocumentsModel.php",
            "src/oihana/models/helpers/getModel.php"
        ]
    }
}
```

Une fois installé, importez directement les classes :

```php
use oihana\models\Model;
use oihana\models\pdo\PDOModel;
use oihana\models\traits\DocumentsTrait;
```

## Vérifier l'installation

```php
require 'vendor/autoload.php';

use DI\Container;
use oihana\models\Model;

$model = new Model( new Container() );
```

## Étapes suivantes

- [Dépendances](dependencies.md)
- [Modèles](../models.md)
