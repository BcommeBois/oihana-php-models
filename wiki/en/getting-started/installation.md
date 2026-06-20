# Installation

## Requirements

- **PHP 8.4 or higher.**
- **[Composer](https://getcomposer.org/).**

The library itself requires no special PHP extension. Transitive dependencies
may require common extensions (e.g. `oihana/php-files` uses `ext-fileinfo`),
which ship with most PHP distributions. A PDO driver is only needed if you use
the PDO-backed models.

## Install via Composer

```bash
composer require oihana/php-models
```

## Autoloading

The package is autoloaded via PSR-4, and its four free-function helpers via
composer `autoload.files`:

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

Once installed, import the classes directly:

```php
use oihana\models\Model;
use oihana\models\pdo\PDOModel;
use oihana\models\traits\DocumentsTrait;
```

## Verify the installation

```php
require 'vendor/autoload.php';

use DI\Container;
use oihana\models\Model;

$model = new Model( new Container() );
```

## Next steps

- [Dependencies](dependencies.md)
- [Models](../models.md)
