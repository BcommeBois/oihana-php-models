<?php

namespace oihana\models;

use oihana\logging\DebugTrait;
use oihana\traits\ToStringTrait;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

use DI\Container;

/**
 * A minimal, container-aware base class for all models of the library.
 *
 * `Model` wires three foundational concerns together and leaves the data-access logic to its
 * subclasses (most notably {@see \oihana\models\pdo\PDOModel}, which mixes in
 * {@see \oihana\models\pdo\PDOTrait}):
 *
 * - **Dependency injection**: the PHP-DI {@see Container} passed to the constructor is stored on
 *   `$container` and reused by the initializers to resolve services declared by name (logger, PDO, …).
 * - **Debug flag**: provided by {@see DebugTrait} and toggled through the `debug` init key.
 * - **Logging**: a PSR-3 {@see LoggerInterface} resolved either directly or from a container id via
 *   the `logger` init key.
 *
 * The constructor follows the library-wide "initialize from an options array" convention: every
 * behaviour is configured through a single associative `$init` array whose keys are documented per
 * initializer. Each `initialize*()` helper returns `$this`, allowing the fluent chaining used below.
 *
 * @example
 * ```php
 * use DI\Container;
 * use oihana\models\Model;
 *
 * $container = new Container();
 *
 * $model = new Model( $container ,
 * [
 *     'debug'  => true ,                 // enable verbose debug output
 *     'logger' => LoggerInterface::class // a logger instance, or its container service id
 * ] );
 * ```
 *
 * @package oihana\models
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class Model
{
    /**
     * Creates a new Model instance and initializes the debug flag, the logger and the mock flag
     * from the given options array.
     *
     * @param Container $container The DI container used to resolve services referenced by name
     *                             (e.g. the logger), and stored on `$container` for later use.
     * @param array{ debug?:bool|null , logger?:LoggerInterface|string|null , mock?:bool|null } $init Optional initialization array with keys:
     *  - **debug** : Whether the debug mode is active (default `false`).
     *  - **logger** : A PSR-3 {@see LoggerInterface} instance, or the container service id of one to resolve.
     *  - **mock** : Whether the model runs in mock mode, e.g. to bypass real I/O in tests (default `false`).
     *
     * @throws ContainerExceptionInterface If an error occurs while retrieving an entry from the dependency-injection container.
     * @throws NotFoundExceptionInterface If no entry is found for the requested identifier in the container.
     *
     * @example
     * ```php
     * $model = new Model( $container , [ 'debug' => true , 'logger' => 'app.logger' ] );
     * ```
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
     * The PHP-DI container reference, used to resolve services by name throughout the model.
     * @var Container
     */
    public Container $container ;
}