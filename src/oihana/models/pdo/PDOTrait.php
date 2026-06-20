<?php

namespace oihana\models\pdo;

use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use PDO;
use ReflectionException;

use Generator;
use PDOException;
use PDOStatement;

use DI\Container;

use oihana\enums\Char;
use oihana\models\enums\ModelParam;
use oihana\models\traits\AlterDocumentTrait;
use oihana\models\traits\BindsTrait;
use oihana\models\traits\SchemaTrait;
use oihana\traits\ContainerTrait;
use oihana\models\traits\ThrowableTrait;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Adds a complete PDO data-access layer to any model: prepared-statement execution, named-parameter
 * binding, schema-aware result mapping and several `fetch*` strategies (single row, full array,
 * streaming generator, single column, column list).
 *
 * Key behaviours:
 * - **Safe binding**: {@see PDOTrait::bindValues()} maps an associative array onto `:name`
 *   placeholders, with optional per-value PDO type hints (`['id' => [5, PDO::PARAM_INT]]`).
 * - **Schema mapping**: when a `$schema` class is set, statements use `PDO::FETCH_CLASS`
 *   (optionally combined with `PDO::FETCH_PROPS_LATE` when `$deferAssignment` is `true`); otherwise
 *   rows fall back to `PDO::FETCH_ASSOC` and are returned as `stdClass`/arrays.
 * - **Post-fetch alteration**: every fetched row passes through {@see AlterDocumentTrait::alter()},
 *   so registered property alterations are applied transparently.
 * - **Resilient by default**: each `fetch*` method swallows exceptions and logs a warning, returning
 *   a neutral value (`null` / `[]`). Pass `$throwable = true` to rethrow instead, e.g. inside
 *   transactions where failures must propagate.
 * - **DI integration**: the {@see PDO} instance is resolved through {@see PDOTrait::initializePDO()},
 *   either directly or from a PSR-11 container by service id.
 *
 * Requires the following traits to be mixed into the host class:
 * - {@see AlterDocumentTrait}
 * - {@see BindsTrait}
 * - {@see \oihana\logging\DebugTrait}
 * - {@see \oihana\traits\ToStringTrait}
 *
 * @package oihana\models\pdo
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait PDOTrait
{
    use AlterDocumentTrait ,
        BindsTrait ,
        ContainerTrait ,
        SchemaTrait ,
        ThrowableTrait ;

    /**
     * Whether schema hydration should run the constructor *before* assigning fetched columns.
     *
     * Only meaningful when a `$schema` class is defined: when `true`, the fetch mode is combined with
     * {@see PDO::FETCH_PROPS_LATE} so the object's constructor runs first and the row values then
     * overwrite the defaults it set.
     *
     * @var bool|null
     */
    public ?bool $deferAssignment = false ;

    /**
     * The PDO connection used to prepare and execute statements, or `null` when none is configured.
     * @var ?PDO
     */
    public ?PDO $pdo = null ;

    /**
     * Binds named parameters onto an already-prepared PDO statement.
     *
     * Each key of `$bindVars` is bound to the matching `:key` placeholder (the leading colon is
     * prepended automatically). A scalar value is bound with PDO's default type inference; an array
     * value is treated as a `[value, type]` pair, letting you force a PDO type such as
     * `PDO::PARAM_INT` or `PDO::PARAM_BOOL`. An empty `$bindVars` is a no-op.
     *
     * @param PDOStatement $statement The prepared statement to bind the values onto.
     * @param array        $bindVars  Associative array of bindings, keyed by placeholder name. Supports:
     *                                 - `['id' => 5]` — bound with default type inference.
     *                                 - `['id' => [5, PDO::PARAM_INT]]` — bound with an explicit PDO type.
     *
     * @return void
     *
     * @example
     * ```php
     * $statement = $pdo->prepare( 'SELECT * FROM users WHERE id = :id AND active = :active' );
     * $this->bindValues( $statement , [ 'id' => [ 42 , PDO::PARAM_INT ] , 'active' => true ] );
     * $statement->execute();
     * ```
     */
    public function bindValues( PDOStatement $statement , array $bindVars = [] ):void
    {
        if( is_array( $bindVars ) && count( $bindVars ) > 0  )
        {
            foreach ( $bindVars as $key => $value )
            {
                if( is_array( $value ) )
                {
                    [ $typedValue , $type ] = $value ;
                    $statement->bindValue( Char::COLON . $key , $typedValue , $type );
                }
                else
                {
                    $statement->bindValue( Char::COLON . $key , $value );
                }
            }
        }
    }

    /**
     * Executes a SELECT query and returns its first row.
     *
     * The row is mapped according to the configured fetch mode: a schema instance when a `$schema`
     * class is set, otherwise a `stdClass` object built from the associative row. The result is then
     * passed through {@see AlterDocumentTrait::alter()} before being returned.
     *
     * When the statement cannot be prepared, fails to execute or returns no row, `null` is returned.
     * On error the exception is caught and logged (or printed in CLI), unless `$throwable` is `true`
     * in which case it is rethrown. The statement cursor is always closed in the `finally` block.
     *
     * @param string $query     The SQL SELECT query to execute, using `:name` placeholders.
     * @param array  $bindVars  Optional named-parameter bindings for the query. Supports:
     *                          - `['id' => 5]`
     *                          - `['id' => [5, PDO::PARAM_INT]]`
     * @param bool   $throwable When `true`, query failures are rethrown instead of being logged and
     *                          swallowed (default `false`).
     *
     * @return mixed The altered result object/schema instance, or `null` if no row is found or the query fails.
     *
     * @throws ContainerExceptionInterface If an error occurs while retrieving an entry from the dependency-injection container.
     * @throws NotFoundExceptionInterface If no entry is found for the requested identifier in the container.
     * @throws DependencyException If the dependency cannot be resolved by the container.
     * @throws NotFoundException If no entry is found for the given identifier in the container.
     * @throws ReflectionException If a class or property cannot be reflected (e.g. during hydration).
     *
     * @example
     * ```php
     * $user = $model->fetch( 'SELECT * FROM users WHERE id = :id' , [ 'id' => 123 ] );
     *
     * if ( $user !== null )
     * {
     *     echo $user->email;
     * }
     * ```
     */
    public function fetch
    (
        string $query          ,
        array  $bindVars  = [] ,
        bool   $throwable = false
    )
    : mixed
    {
        $statement = null ;

        try
        {
            $statement = $this->pdo?->prepare( $query ) ;

            if( !$statement instanceof PDOStatement )
            {
                return null ;
            }

            $this->bindValues( $statement , $bindVars ) ;

            if( !$statement->execute() )
            {
                return null ;
            }

            $this->initializeDefaultFetchMode( $statement ) ;

            $row    = $statement->fetch() ;
            $result = $row === false ? null : (object) $row ;

            return $result !== null ? $this->alter( $result ) : null ;
        }
        catch ( Exception $exception )
        {
            if( $throwable )
            {
                throw $exception ;
            }

            if ( PHP_SAPI === 'cli' )
            {
                echo PHP_EOL  ;
                echo '-------------- PDOTrait::fetch failed ---------------------------------------' . PHP_EOL  ;
                echo "PDOTrait::fetch query    : " . $query . PHP_EOL . PHP_EOL  ;
                echo "PDOTrait::fetch bindVars : " . json_encode( $bindVars ) . PHP_EOL . PHP_EOL  ;
                echo "PDOTrait::fetch exception message : " . $exception->getMessage() . PHP_EOL ;
                echo '-----------------------------------------------------------------------------' . PHP_EOL  ;
            }
            else
            {
                // Unreachable under the CLI test harness (PHP_SAPI is always 'cli' here).
                // @codeCoverageIgnoreStart
                $this->warning( __METHOD__ . ' failed, ' . $exception->getMessage() ) ;
                // @codeCoverageIgnoreEnd
            }
        }
        finally
        {
            if( $statement instanceof PDOStatement )
            {
                $statement->closeCursor() ;
            }

            $statement = null ;
        }

        return null ;
    }

    /**
     * Executes a query and returns all of its rows at once.
     *
     * Depending on the configured fetch mode, rows are returned as schema instances (when a `$schema`
     * class is set) or as associative arrays. The non-empty result set is passed as a whole through
     * {@see AlterDocumentTrait::alter()} so registered alterations apply to every element.
     *
     * An empty array is returned when the statement cannot be prepared, fails to execute or yields no
     * rows. Errors are caught and logged unless `$throwable` is `true`. The cursor is always closed.
     *
     * @param string $query     The SQL query to execute, using `:name` placeholders.
     * @param array  $bindVars  Optional named-parameter bindings for the query. Supports:
     *                          - `['id' => 5]`
     *                          - `['id' => [5, PDO::PARAM_INT]]`
     * @param bool   $throwable When `true`, query failures are rethrown instead of being logged and
     *                          swallowed (default `false`).
     *
     * @return array The altered list of results, or an empty array if the query yields nothing or
     *               fails while `$throwable` is `false`.
     *
     * @throws ContainerExceptionInterface If an error occurs while retrieving an entry from the dependency-injection container.
     * @throws NotFoundExceptionInterface If no entry is found for the requested identifier in the container.
     * @throws DependencyException If the dependency cannot be resolved by the container.
     * @throws NotFoundException If no entry is found for the given identifier in the container.
     * @throws ReflectionException If a class or property cannot be reflected (e.g. during hydration).
     *
     * @example
     * ```php
     * $admins = $model->fetchAll(
     *     'SELECT * FROM users WHERE role = :role' ,
     *     [ 'role' => 'admin' ]
     * );
     *
     * foreach ( $admins as $admin )
     * {
     *     // ...
     * }
     * ```
     */
    public function fetchAll
    (
        string $query             ,
        array  $bindVars  = []    ,
        bool   $throwable = false
    )
    :array
    {
        $result    = [] ;
        $statement = null ;
        try
        {
            $statement = $this->pdo?->prepare( $query ) ;

            if ( !$statement instanceof PDOStatement )
            {
                return [] ;
            }

            $this->bindValues( $statement , $bindVars ) ;

            if ( !$statement->execute() )
            {
                return [] ;
            }

            $this->initializeDefaultFetchMode( $statement ) ;

            $result = $statement->fetchAll() ;

            if( !empty( $result ) )
            {
                $result = $this->alter( $result ) ;
            }

        }
        catch ( Exception $exception )
        {
            if( $throwable )
            {
                throw $exception ;
            }

            $this->warning( __METHOD__ . ' failed, ' . $exception->getMessage() ) ;
        }
        finally
        {
            if ( $statement instanceof PDOStatement )
            {
                $statement->closeCursor() ;
            }
            $statement = null ;
        }

        return $result ;
    }

    /**
     * Executes a SELECT query and streams its rows one by one through a generator.
     *
     * This is the memory-efficient counterpart of {@see PDOTrait::fetchAll()}: rows are never
     * accumulated in an array. Each row is cast to an object (or hydrated into a schema instance via
     * the configured fetch mode), passed through {@see AlterDocumentTrait::alter()} and yielded
     * immediately. The cursor is closed once iteration completes or the generator is discarded.
     *
     * If the statement cannot be prepared or fails to execute, the generator simply yields nothing.
     * Errors raised during iteration are logged unless `$throwable` is `true`.
     *
     * @param string $query     The SQL query to execute, using `:name` placeholders.
     * @param array  $bindVars  Optional named-parameter bindings for the query (see {@see PDOTrait::bindValues()}).
     * @param bool   $throwable When `true`, query failures are rethrown instead of being logged and
     *                          swallowed (default `false`).
     *
     * @return Generator<object> A generator yielding the altered rows one at a time.
     *
     * @throws ContainerExceptionInterface If an error occurs while retrieving an entry from the dependency-injection container.
     * @throws NotFoundExceptionInterface If no entry is found for the requested identifier in the container.
     * @throws DependencyException If the dependency cannot be resolved by the container.
     * @throws NotFoundException If no entry is found for the given identifier in the container.
     * @throws ReflectionException If a class or property cannot be reflected (e.g. during hydration).
     *
     * @example
     * ```php
     * // Iterate over a million rows without exhausting memory
     * foreach ( $model->fetchAllAsGenerator( 'SELECT * FROM events' ) as $event )
     * {
     *     $this->process( $event );
     * }
     * ```
     */
    public function fetchAllAsGenerator
    (
        string $query ,
        array  $bindVars  = [] ,
        bool   $throwable = false
    )
    : Generator
    {
        try
        {
            $statement = $this->pdo?->prepare( $query ) ;

            if ( !$statement instanceof PDOStatement )
            {
                return ;
            }

            $this->bindValues( $statement , $bindVars ) ;

            if ( !$statement->execute() )
            {
                return ;
            }

            $this->initializeDefaultFetchMode( $statement ) ;

            try
            {
                while ( $row = $statement->fetch() )
                {
                    $result        = (object) $row ;
                    $alteredResult = $this->alter( $result ) ;
                    yield $alteredResult ;
                }
            }
            finally
            {
                $statement->closeCursor() ;
            }
        }
        catch ( Exception $exception )
        {
            if( $throwable )
            {
                throw $exception ;
            }

            $this->warning(__METHOD__ . ' failed, ' . $exception->getMessage() ) ;
        }
        finally
        {
            $statement = null ;
        }
    }

    /**
     * Executes a query and returns the value of a single column from its first row.
     *
     * Unlike the other `fetch*` helpers this one does not apply schema mapping nor alterations — it
     * returns the raw scalar produced by {@see PDOStatement::fetchColumn()}. Handy for `COUNT(*)`,
     * `MAX(...)`, an existence check or fetching one field.
     *
     * Returns `null` when the statement cannot be prepared, fails to execute or has no row. Errors
     * are logged and swallowed unless `$throwable` is `true`. The cursor is always closed.
     *
     * @param string $query     The SQL query to execute, using `:name` placeholders.
     * @param array  $bindVars  Optional named-parameter bindings for the query (see {@see PDOTrait::bindValues()}).
     * @param int    $column    Zero-based index of the column to return from the first row (default `0`).
     * @param bool   $throwable When `true`, query failures are rethrown instead of being logged and
     *                          swallowed (default `false`).
     *
     * @return mixed The column value, or `null` if no row is found or the query fails.
     *
     * @throws Exception If the SQL statement fails to prepare or execute and `$throwable` is `true`.
     *
     * @example
     * ```php
     * $total = (int) $model->fetchColumn(
     *     'SELECT COUNT(*) FROM users WHERE active = :active' ,
     *     [ 'active' => true ]
     * );
     * ```
     */
    public function fetchColumn
    (
        string $query ,
        array  $bindVars  = [] ,
        int    $column    = 0 ,
        bool   $throwable = false
    )
    :mixed
    {
        $statement = null;

        try
        {
            $statement = $this->pdo?->prepare( $query ) ;

            if ( !$statement instanceof PDOStatement )
            {
                return null ;
            }

            $this->bindValues( $statement , $bindVars ) ;

            if ( !$statement->execute() )
            {
                return null  ;
            }

            return $statement->fetchColumn( $column ) ;
        }
        catch ( Exception $exception )
        {
            if ($throwable)
            {
                throw $exception;
            }

            $this->warning(__METHOD__ . ' failed, ' . $exception->getMessage() ) ;

            return null  ;
        }
        finally
        {
            if ( $statement instanceof PDOStatement )
            {
                $statement->closeCursor() ;
            }
            $statement = null ;
        }
    }

    /**
     * Executes a query and returns the first column of every row as a flat list.
     *
     * Uses `PDO::FETCH_COLUMN`, so a single-column SELECT yields a simple list of scalar values. Like
     * {@see PDOTrait::fetchColumn()} it applies neither schema mapping nor alterations.
     *
     * Returns an empty array when the statement cannot be prepared, fails to execute or has no rows.
     * Errors are logged and swallowed unless `$throwable` is `true`. The cursor is always closed.
     *
     * @param string $query     The SQL query to execute, using `:name` placeholders.
     * @param array  $bindVars  Optional named-parameter bindings for the query (see {@see PDOTrait::bindValues()}).
     * @param bool   $throwable When `true`, query failures are rethrown instead of being logged and
     *                          swallowed (default `false`).
     *
     * @return array<int, string> The list of first-column values, or an empty array on failure.
     *
     * @throws Exception If the SQL statement fails to prepare or execute and `$throwable` is `true`.
     *
     * @example
     * ```php
     * $emails = $model->fetchColumnArray( 'SELECT email FROM users WHERE active = 1' );
     * // [ 'a@example.com' , 'b@example.com' , ... ]
     * ```
     */
    public function fetchColumnArray
    (
        string $query ,
        array  $bindVars  = [] ,
        bool   $throwable = false
    )
    : array
    {
        $statement = null ;

        try
        {
            $statement = $this->pdo?->prepare( $query ) ;

            if ( !$statement instanceof PDOStatement )
            {
                return [] ;
            }

            $this->bindValues( $statement, $bindVars ) ;

            if ( !$statement->execute() )
            {
                return [] ;
            }

            return $statement->fetchAll( PDO::FETCH_COLUMN ) ;
        }
        catch ( Exception $exception )
        {
            if ($throwable)
            {
                throw $exception;
            }

            $this->warning(__METHOD__ . ' failed, ' . $exception->getMessage() );
            return [] ;
        }
        finally
        {
            if ( $statement instanceof PDOStatement )
            {
                $statement->closeCursor() ;
            }
            $statement = null ;
        }
    }

    /**
     * Configures the fetch mode of a statement just before rows are read.
     *
     * When `$schema` names an existing class, rows are hydrated into instances of it via
     * `PDO::FETCH_CLASS`, combined with `PDO::FETCH_PROPS_LATE` when `$deferAssignment` is `true`
     * (so the constructor runs before columns are assigned). Otherwise the statement falls back to
     * `PDO::FETCH_ASSOC`. Called internally by every `fetch*` helper.
     *
     * @param PDOStatement $statement The prepared, executed statement to configure.
     *
     * @return void
     */
    public function initializeDefaultFetchMode( PDOStatement $statement ):void
    {
        if( is_string( $this->schema ) && class_exists( $this->schema ) )
        {
            $mode = PDO::FETCH_CLASS ;
            if( $this->deferAssignment )
            {
                $mode |= PDO::FETCH_PROPS_LATE ;
            }
            $statement->setFetchMode( $mode , $this->schema ) ;
        }
        else
        {
            $statement->setFetchMode( PDO::FETCH_ASSOC ) ;
        }
    }

    /**
     * Initializes the `$deferAssignment` property from an options array.
     *
     * Reads the {@see ModelParam::DEFER_ASSIGNMENT} key when present, falling back to `false`.
     *
     * @param array $init Initialization options; the `deferAssignment` boolean key sets the property.
     *
     * @return static The current instance, i.e. `$this`, for fluent chaining.
     */
    public function initializeDeferAssignment( array $init = [] ):static
    {
        $this->deferAssignment = $init[ ModelParam::DEFER_ASSIGNMENT ] ?? false ;
        return $this ;
    }

    /**
     * Initializes the `$pdo` connection from an options array or the DI container.
     *
     * The {@see ModelParam::PDO} key may hold a {@see PDO} instance directly, or a string service id.
     * When it is a string and the given container declares that id, the service is resolved from it.
     * Any value that is not ultimately a {@see PDO} instance leaves `$pdo` set to `null`.
     *
     * @param array          $init      Configuration array; the `pdo` key holds a {@see PDO} instance or a container id.
     * @param Container|null $container Optional DI container used to resolve a string PDO service id.
     *
     * @return static The current instance, i.e. `$this`, for fluent chaining.
     *
     * @throws ContainerExceptionInterface If an error occurs while retrieving an entry from the dependency-injection container.
     * @throws NotFoundExceptionInterface If no entry is found for the requested identifier in the container.
     */
    public function initializePDO( array $init = [] , ?Container $container = null ) :static
    {
        $pdo = $init[ ModelParam::PDO ] ?? null  ;
        if( isset( $container ) && is_string( $pdo ) && $container->has( $pdo ) )
        {
            $pdo = $container->get( $pdo ) ;
        }
        $this->pdo = $pdo instanceof PDO ? $pdo : null ;
        return $this ;
    }

    /**
     * Indicates whether a live PDO connection is currently available.
     *
     * Returns `false` when no PDO instance is configured. Otherwise it queries
     * `PDO::ATTR_CONNECTION_STATUS`; a non-`null` status means the connection is up. Any
     * {@see PDOException} raised while reading the attribute is treated as "not connected".
     *
     * @return bool `true` if a PDO instance is set and reports a connection status, `false` otherwise.
     */
    public function isConnected(): bool
    {
        if ( !$this->pdo instanceof PDO )
        {
            return false ;
        }

        try
        {
            return $this->pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS ) !== null ;
        }
        catch ( PDOException )
        {
            return false;
        }
    }
}