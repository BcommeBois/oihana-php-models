<?php

namespace oihana\models;

use oihana\logging\DebugTrait;
use oihana\traits\ToStringTrait;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

use DI\Container;

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
class Model
{
    /**
     * Creates a new Model instance.
     *
     * @param Container $container The DI container to retrieve services like PDO and logger.
     * @param array{ debug:bool|null , logger:LoggerInterface|string|null , mock:bool|null } $init Optional initialization array with keys:
     *  - **debug** : Indicates if the debug mode is active (Default false).
     *  - **logger** : The optional PSR3 LoggerInterface reference or the name of the reference in the DI Container.
     *  - **mock** : Indicates if the model use a mock process (Default false).
     *
     * @throws ContainerExceptionInterface If container service retrieval fails.
     * @throws NotFoundExceptionInterface If container service not found.
     */
    public function __construct( Container $container , array $init = [] )
    {
        $this->container = $container ;
        $this->initializeDebug( $init )
             ->initializeLogger( $init , $container )
             ->initializeMock( $init ) ;
    }

    use DebugTrait ,
        ToStringTrait ;

    /**
     * The DI container reference.
     * @var Container
     */
    public Container $container ;
}