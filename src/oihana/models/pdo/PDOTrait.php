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
 * Provides methods for binding values, executing queries, and retrieving results using PDO.
 * Supports schema-based result mapping and integration with dependency injection.
 *
 * Requires the following traits:
 * - AlterDocumentTrait
 * - BindsTrait
 * - oihana\logging\DebugTrait
 * - ToStringTrait
 *
 * @package oihana\traits
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
     * Indicates if the the constructor is called before setting properties.
     * Only if the schema property is defined.
     * @var bool|null
     */
    public ?bool $deferAssignment = false ;

    /**
     * The PDO reference.
     * @var ?PDO
     */
    public ?PDO $pdo = null ;

    /**
     * Bind named parameters to a prepared PDO statement.
     *
     * @param PDOStatement $statement  The PDO statement.
     * @param array        $bindVars   Associative array of bindings. Supports:
     *                                 - ['id' => 5]
     *                                 - ['id' => [5, PDO::PARAM_INT]]
     * @return void
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
     * Execute a SELECT query and fetch a single result.
     * The result is returned as an object or as a mapped schema class if defined.
     * Alteration is applied via AlterDocumentTrait.
     *
     * @param string $query The SQL SELECT query to execute.
     * @param array $bindVars Optional named parameter bindings for the query.
     * Supports:
     * - ['id' => 5]
     * - ['id' => [5, PDO::PARAM_INT]]
     *
     * @param bool $throwable Whether to rethrow exceptions instead of handling them internally (default: false).
     *
     * @return mixed|null The mapped result object, or null if no row is found.
     *
     * @throws ContainerExceptionInterface If dependency resolution fails.
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface  If a required service is not found.
     * @throws ReflectionException
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
     * Execute a query and fetch all results.
     *
     * Results are returned as an array of associative arrays or schema instances.
     *
     * Alteration is applied via AlterDocumentTrait.
     *
     * @param string $query   The query to execute.
     * @param array $bindVars Optional named parameter bindings for the query.
     * Supports:
     * - ['id' => 5]
     * - ['id' => [5, PDO::PARAM_INT]]
     * @param bool $throwable Whether to rethrow exceptions instead of handling them internally (default: false).
     *
     * @return array An array of results. Returns an empty array if the query fails and `$throwable` is false.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ReflectionException
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
     * Execute a SELECT query and fetch all results as a generator.
     * Results are yielded one by one as objects or schema instances.
     * Alteration is applied via AlterDocumentTrait.
     *
     * @param string $query     The SQL query to execute.
     * @param array  $bindVars  Optional bindings for the query.
     * @param bool   $throwable Whether to rethrow exceptions instead of handling them internally (default: false).
     *
     * @return Generator<object> A generator yielding results one by one.
     *
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
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
     * Execute a query and return the value of a single column from the first row.
     *
     * @param string $query     The SQL query to execute.
     * @param array  $bindVars  Optional bindings for the query.
     * @param int    $column    Column index (0-based) to return from the first row.
     * @param bool   $throwable Whether to rethrow exceptions instead of handling them internally (default: false).
     *
     * @return mixed The column value or null if the query fails.
     *
     * @throws Exception
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
     * Fetch a list of single-column results.
     *
     * @param string $query The SQL query to execute.
     * @param array $bindVars Optional bindings for the query.
     * @param bool $throwable Whether to rethrow exceptions instead of handling them internally (default: false).
     *
     * @return array<int, string>
     *
     * @throws Exception
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
     * Set the default fetch mode on the statement.
     * Uses FETCH_ASSOC by default or FETCH_CLASS (with optional FETCH_PROPS_LATE)
     * if a schema class is defined and exists.
     *
     * @param PDOStatement $statement  The PDO statement to configure.
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
     * Initialize the 'deferAssignment' property.
     * @param array $init
     * @return static
     */
    public function initializeDeferAssignment( array $init = [] ):static
    {
        $this->deferAssignment = $init[ ModelParam::DEFER_ASSIGNMENT ] ?? false ;
        return $this ;
    }

    /**
     * Initialize the PDO instance from a config array or dependency injection container.
     *
     * @param array         $init       Configuration array. Expects ModelParam::PDO ('pdo') as key.
     * @param Container|null $container Optional DI container to resolve the PDO service.
     *
     * @return static
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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
     * Indicates if the PDO is connected.
     * @return bool
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