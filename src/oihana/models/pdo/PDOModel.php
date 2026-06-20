<?php

namespace oihana\models\pdo;

use PDO;

use DI\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use oihana\models\Model;

/**
 * A base model class that integrates a PDO instance with dependency injection container support.
 * This class uses the PDOTrait to provide PDO-related database operations, binding, and fetching.
 *
 * The model can be initialized with configuration options such as alters, binds, schema, defer assignment,
 * logger, mock objects, and the PDO instance itself.
 *
 * @throws ContainerExceptionInterface If there is a problem retrieving services from the container.
 * @throws NotFoundExceptionInterface If a required service is not found in the container.
 *
 * @package oihana\models
 *
 * @property Container $container The dependency injection container instance.
 *
 * @method mixed fetch(string $query, array $bindVars = []) Fetch a single record from the database.
 * @method array fetchAll(string $query, array $bindVars = []) Fetch all matching records from the database.
 * @method mixed fetchColumn(string $query, array $bindVars = [], int $column = 0) Fetch a single column from the first row.
 *
 * @example
 * ```php
 * use DI\Container;
 * use oihana\models\PDOModel;
 *
 * $container = new Container();
 *
 * // Configuration array with optional parameters
 * $config =
 * [
 *     'deferAssignment' => true,
 *     'pdo'             => 'my_pdo_service', // or a PDO instance
 *     'schema'          => MyEntity::class,
 * ];
 *
 * // Instantiate the model with the container and configuration
 * $model = new PDOModel( $container , $config ) ;
 *
 * // Fetch a single record
 * $record = $model->fetch('SELECT * FROM users WHERE id = :id', ['id' => 123]);
 *
 * // Fetch all records
 * $records = $model->fetchAll('SELECT * FROM users');
 * ```
 *
 * @package oihana\models
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class PDOModel extends Model
{
    /**
     * Creates a new PDOModel instance.
     *
     * Sets internal properties from the provided configuration array and initializes logger, mock, and PDO.
     *
     * @param Container $container The DI container to retrieve services like PDO and logger.
     * @param array{
     *   alters?          : array|null ,
     *   binds?           : array|null ,
     *   deferAssignment? : bool|null ,
     *   schema?          : string|null ,
     *   pdo?             : PDO|string|null
     * } $init Optional initialization array with keys:
     *   - ModelParam::ALTERS           : array of alterations to apply
     *   - ModelParam::BINDS            : array of binds for queries
     *   - ModelParam::DEFER_ASSIGNMENT : bool whether to defer property assignment on fetch
     *   - ModelParam::SCHEMA           : string class name of schema for fetch mode
     *   - ModelParam::PDO              : PDO instance or service name in container
     *
     * @throws ContainerExceptionInterface If container service retrieval fails.
     * @throws NotFoundExceptionInterface If container service not found.
     */
    public function __construct( Container $container , array $init = [] )
    {
        parent::__construct( $container , $init ) ;
        $this->initializeAlters          ( $init )
             ->initializeBinds           ( $init )
             ->initializeDeferAssignment ( $init )
             ->initializeSchema          ( $init )
             ->initializeThrowable       ( $init )
             ->initializePDO             ( $init , $container ) ;
    }

    use PDOTrait ;
}
