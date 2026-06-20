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
     * Replaces an identifier value by the document it references, loaded through a Documents model.
     *
     * The model class name is taken from `$definition[0]` and resolved via
     * {@see getDocumentsModel()} (optionally using the DI container). The model's `get()`
     * method is then called with the lookup key `$definition[1]` and the current `$value`.
     * A `null` value short-circuits and is returned untouched, and any failure raised while
     * fetching the document is swallowed and turned into a `null` result.
     *
     * @param mixed      $value      The identifier to resolve. A `null` value is returned as-is.
     * @param array      $definition The lookup definition: `[ 0 => model class/identifier ,
     *                               1 => lookup key field ]`. Both default to `null`.
     * @param ?Container $container  Optional DI container used by {@see getDocumentsModel()} to
     *                               resolve the model instance from a service definition.
     * @param bool       $modified   Reference flag set to `true` once a document has been fetched.
     *
     * @return mixed The loaded document, `null` when the lookup failed, or the original `$value`
     *               when it was `null` or no model could be resolved.
     *
     * @throws ContainerExceptionInterface If an error occurs while retrieving an entry from the dependency-injection container.
     * @throws DependencyException         If the dependency cannot be resolved by the container.
     * @throws NotFoundException           If no entry is found for the given identifier in the container.
     * @throws NotFoundExceptionInterface  If no entry is found for the requested identifier in the container.
     *
     * @example
     * ```php
     * class MyMapper
     * {
     *     use AlterGetDocumentPropertyTrait;
     * }
     *
     * $mapper   = new MyMapper();
     * $modified = false;
     *
     * // Replace a user id by the full user document loaded from the 'UserModel' service
     * $user = $mapper->alterGetDocument( 42 , [ 'UserModel' , 'id' ] , $container , $modified );
     * // $user === <document whose id === 42> (or null if not found)
     * // $modified === true
     *
     * // A null identifier is returned untouched
     * $none = $mapper->alterGetDocument( null , [ 'UserModel' , 'id' ] , $container , $modified );
     * // $none === null
     * ```
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