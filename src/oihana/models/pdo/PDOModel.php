<?php

namespace oihana\models\pdo;

use PDO;

use DI\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use oihana\models\Model;

/**
 * A ready-to-use, PDO-backed model that turns SQL queries into objects, schema instances or scalars.
 *
 * `PDOModel` extends {@see Model} (DI container + debug + logger) and mixes in {@see PDOTrait}, which
 * supplies all the database plumbing: prepared-statement execution, named-parameter binding, the
 * various `fetch*` helpers, schema-based result mapping and generator-based streaming.
 *
 * Everything is configured through the constructor `$init` array, including the {@see PDO} instance
 * itself which may be passed directly or referenced by its container service id:
 *
 * - **alters** : a map of property alterations applied to each fetched row (see {@see AlterDocumentTrait}).
 * - **binds** : default named-parameter bindings reused across queries (see {@see BindsTrait}).
 * - **deferAssignment** : when a schema is set, hydrate via `FETCH_PROPS_LATE` (constructor runs first).
 * - **schema** : a class name; rows are then returned as instances of that class instead of `stdClass`.
 * - **pdo** : a {@see PDO} instance, or the container id of one to resolve.
 *
 * @example
 * ```php
 * use DI\Container;
 * use oihana\models\pdo\PDOModel;
 *
 * $container = new Container();
 *
 * // Configuration array with optional parameters
 * $config =
 * [
 *     'deferAssignment' => true,
 *     'pdo'             => 'my_pdo_service', // a PDO instance, or its container service id
 *     'schema'          => MyEntity::class,  // rows are hydrated into MyEntity instances
 * ];
 *
 * // Instantiate the model with the container and configuration
 * $model = new PDOModel( $container , $config ) ;
 *
 * // Fetch a single record (returned as a MyEntity instance thanks to the schema)
 * $record = $model->fetch( 'SELECT * FROM users WHERE id = :id' , [ 'id' => 123 ] );
 *
 * // Fetch all records
 * $records = $model->fetchAll( 'SELECT * FROM users' );
 *
 * // Stream a large result set without loading it all in memory
 * foreach ( $model->fetchAllAsGenerator( 'SELECT * FROM logs' ) as $row )
 * {
 *     // process $row
 * }
 * ```
 *
 * @package oihana\models\pdo
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class PDOModel extends Model
{
    /**
     * Creates a new PDOModel instance.
     *
     * Runs the parent {@see Model} initializers (debug, logger, mock), then configures the
     * PDO-specific behaviour from the same `$init` array, in this order: alterations, default binds,
     * deferred assignment, schema, throwable flag and finally the PDO instance (resolved from the
     * container when given as a string id).
     *
     * @param Container $container The DI container used to resolve services such as the PDO instance and the logger.
     * @param array{
     *   alters?          : array|null ,
     *   binds?           : array|null ,
     *   deferAssignment? : bool|null ,
     *   schema?          : string|null ,
     *   pdo?             : PDO|string|null
     * } $init Optional initialization array with keys:
     *   - **alters** ({@see ModelParam::ALTERS}) : map of property alterations applied to each fetched row.
     *   - **binds** ({@see ModelParam::BINDS}) : default named-parameter bindings reused across queries.
     *   - **deferAssignment** ({@see ModelParam::DEFER_ASSIGNMENT}) : when `true` and a schema is set, hydrate with `FETCH_PROPS_LATE`.
     *   - **schema** ({@see ModelParam::SCHEMA}) : class name used as the fetch target instead of `stdClass`.
     *   - **pdo** ({@see ModelParam::PDO}) : a {@see PDO} instance, or the container service id of one to resolve.
     *
     * @throws ContainerExceptionInterface If an error occurs while retrieving an entry from the dependency-injection container.
     * @throws NotFoundExceptionInterface If no entry is found for the requested identifier in the container.
     *
     * @example
     * ```php
     * $model = new PDOModel( $container ,
     * [
     *     'pdo'    => 'db.pdo' ,          // resolved from the container
     *     'schema' => User::class ,       // rows hydrated as User instances
     *     'binds'  => [ 'tenant' => 1 ] , // default bindings
     * ] );
     * ```
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
