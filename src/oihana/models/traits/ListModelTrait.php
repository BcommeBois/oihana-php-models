<?php

namespace oihana\models\traits ;

use UnexpectedValueException;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use oihana\enums\Char;
use oihana\models\enums\ModelParam;
use oihana\models\interfaces\ListModel;

/**
 * Provides a `list` reference exposing a {@see ListModel} to the host class.
 *
 * Mix this trait into a model or service that delegates "list" operations (paginated reads,
 * collection queries, etc.) to a {@see ListModel}. The reference can be injected directly or
 * resolved from a DI container service id during initialization, and {@see assertListModel()}
 * guards against using it before it is set.
 *
 * @package oihana\models\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait ListModelTrait
{
    /**
     * The list model reference used to perform collection/list operations.
     *
     * @var ?ListModel
     */
    public ?ListModel $list ;

    /**
     * Asserts that the `$list` property has been set.
     *
     * @return void
     *
     * @throws UnexpectedValueException If the `list` property is not set.
     */
    protected function assertListModel():void
    {
        if( !isset( $this->list ) )
        {
            throw new UnexpectedValueException( 'The list property is not set.' ) ;
        }
    }

    /**
     * Initializes the `$list` reference from an initialization array.
     *
     * The value is read from the {@see ModelParam::LIST} key. When it is a non-empty string and the
     * container holds a matching entry, the service is resolved. The reference is set only if the
     * resolved value is a {@see ListModel}; otherwise it is set to `null`.
     *
     * @param array                    $init      Initialization options (key: `ModelParam::LIST`).
     * @param ContainerInterface|null  $container Optional DI container used to resolve a string service id.
     *
     * @return static The current instance, for fluent chaining.
     *
     * @throws ContainerExceptionInterface If an error occurs while retrieving an entry from the dependency-injection container.
     * @throws NotFoundExceptionInterface If no entry is found for the requested identifier in the container.
     */
    protected function initializeListModel( array $init = [] , ?ContainerInterface $container = null ) :static
    {
        $list = $init[ ModelParam::LIST ] ?? null ;
        if( is_string( $list ) && $list != Char::EMPTY && $container?->has( $list ) )
        {
            $list = $container->get( $list ) ;
        }
        $this->list = $list instanceof ListModel ? $list : null ;
        return $this;
    }
}