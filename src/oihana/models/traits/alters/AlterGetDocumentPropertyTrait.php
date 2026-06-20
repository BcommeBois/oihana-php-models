<?php

namespace oihana\models\traits\alters;

use Throwable;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use oihana\models\enums\ModelParam;

use function oihana\models\helpers\getDocumentsModel;

/**
 * Provides logic to retrieve a document using a Documents model based on a given value and definition.
 * This trait depends on the `DocumentsTrait` to access the document model.
 *
 * The main method `alterGetDocument()` is typically used as part of a data transformation or hydration process
 * where a scalar or identifier is replaced by a fully loaded document instance.
 *
 * ### Usage example:
 *
 * ```php
 * class MyMapper {
 *     use AlterGetDocumentPropertyTrait;
 * }
 *
 * $mapper = new MyMapper();
 * $doc = $mapper->alterGetDocument(42, ['UserModel', 'id'], $modified);
 *
 * if ($modified) {
 *     echo "Document was loaded successfully.";
 * }
 * ```
 *
 * @package oihana\models\traits\alters
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait AlterGetDocumentPropertyTrait
{
    /**
     * Gets a document with a Documents model.
     * @param mixed         $value
     * @param array         $definition
     * @param ?Container    $container     DI container for resolving base URL from service definitions.
     * @param bool          $modified
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function alterGetDocument
    (
        mixed      $value ,
        array      $definition = []    ,
        ?Container $container  = null  ,
        bool       &$modified  = false
    )
    : mixed
    {
        if( isset( $value ) )
        {
            $model = getDocumentsModel( $definition[0] ?? null , $container ) ;
            if( isset( $model ) )
            {
                try
                {
                    $newValue = $model->get
                    ([
                        ModelParam::KEY   => $definition[1] ?? null ,
                        ModelParam::VALUE => $value
                    ]) ;
                    $modified = true ;
                    return $newValue ;
                }
                catch( Throwable )
                {
                    return null ; // return null if the get method failed
                }
            }
        }
        return $value ;
    }
}