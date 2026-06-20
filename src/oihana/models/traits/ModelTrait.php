<?php

namespace oihana\models\traits;

use DI\DependencyException;
use DI\NotFoundException;

use oihana\models\enums\ModelParam;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use oihana\models\interfaces\DocumentsModel;
use UnexpectedValueException;

/**
 * Provides a `model` reference exposing a {@see DocumentsModel} to the host class.
 *
 * Mix this trait into a controller or service that operates on a single document model. It builds
 * on {@see DocumentsTrait} to resolve the model from a direct instance or a DI container service id
 * during initialization, and offers {@see assertModel()} to guard against using it before it is set.
 *
 * @package oihana\models\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait ModelTrait
{
    use DocumentsTrait ;

    /**
     * The document model reference.
     *
     * @var ?DocumentsModel
     */
    public ?DocumentsModel $model = null ;

    /**
     * Asserts that the `$model` property has been set.
     *
     * @return void
     *
     * @throws UnexpectedValueException If the 'model' property is not set.
     */
    protected function assertModel():void
    {
        if( !isset( $this->model ) )
        {
            throw new UnexpectedValueException( 'The `model` property is not set.' ) ;
        }
    }

    /**
     * Initializes the `$model` reference from an initialization array.
     *
     * The value is read from the {@see ModelParam::MODEL} key (falling back to the current `$model`)
     * and resolved through {@see DocumentsTrait::getDocumentsModel()}, which accepts either a ready
     * instance or a container service id.
     *
     * @param array $init Initialization options (key: `ModelParam::MODEL`).
     *
     * @return static The current instance, for fluent chaining.
     *
     * @throws DependencyException If the dependency cannot be resolved by the container.
     * @throws NotFoundException If no entry is found for the given identifier in the container.
     * @throws ContainerExceptionInterface If an error occurs while retrieving an entry from the dependency-injection container.
     * @throws NotFoundExceptionInterface If no entry is found for the requested identifier in the container.
     */
    protected function initializeModel( array $init = [] ):static
    {
        $this->model = $this->getDocumentsModel( $init[ ModelParam::MODEL ] ?? $this->model ) ;
        return $this ;
    }
}