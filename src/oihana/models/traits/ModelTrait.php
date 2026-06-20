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
 * Defines a Document model properties in your class.
 * @package oihana\models\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait ModelTrait
{
    use DocumentsTrait ;

    /**
     * The model reference.
     */
    public ?DocumentsModel $model = null ;

    /**
     * Asserts the existence of the `model` property.
     * @return void
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
     * Initialize the openEdge model.
     * @param array $init
     * @return static
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function initializeModel( array $init = [] ):static
    {
        $this->model = $this->getDocumentsModel( $init[ ModelParam::MODEL ] ?? $this->model ) ;
        return $this ;
    }
}