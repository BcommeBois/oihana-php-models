<?php

namespace oihana\models\traits;

use Exception;

use DI\DependencyException;
use DI\NotFoundException;

use oihana\models\enums\ModelParam;
use oihana\traits\ContainerTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use oihana\enums\Char;
use oihana\exceptions\http\Error404;

use oihana\models\interfaces\DocumentsModel;
use oihana\models\interfaces\ExistModel;

/**
 * Provides helpers to resolve and validate document models through the DI container.
 *
 * Mix this trait into controllers or services that depend on a {@see DocumentsModel}. It can:
 * - resolve a model from either a ready instance or a container service id
 *   ({@see getDocumentsModel()});
 * - assert that a referenced document actually exists in a given {@see ExistModel},
 *   raising an {@see Error404} otherwise ({@see assertExistInModel()}).
 *
 * It relies on {@see ContainerTrait} for container access.
 *
 * @package oihana\models\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait DocumentsTrait
{
    use ContainerTrait ;

    /**
     * Asserts that a document (identified by an id) exists in the given model.
     *
     * The id is taken from `$document->{$key}` when an object is passed, or used directly when a
     * scalar is passed. The model is queried through {@see ExistModel::exist()} with the id bound
     * under `$key`. Any failure — invalid id, lookup exception, or non-existent document — results
     * in an {@see Error404}.
     *
     * @param object|string|int|null $document The document instance or the raw id to validate.
     * @param ExistModel             $model    The model used to check existence.
     * @param string                 $name     Human-readable resource name, injected into the error message.
     * @param string|null            $key      The property/bind key holding the identifier. Defaults to `'id'`.
     *
     * @return void
     *
     * @throws Error404 If the id is invalid, the existence check fails, or the document does not exist.
     *
     * @example
     * ```php
     * // Throws Error404 unless a product with id 105997 exists.
     * $this->assertExistInModel( 105997 , $productModel , 'product' );
     * ```
     */
    public function assertExistInModel
    (
        null|string|int|object $document ,
        ExistModel $model ,
        string     $name = Char::EMPTY ,
        ?string    $key  = 'id'
    )
    :void
    {
        try
        {
            $id = is_object( $document ) ? $document->{ $key } : $document ;
            if( ( ( is_string( $id ) && $id != Char::EMPTY ) || is_int( $id ) ) && $model->exist( [ ModelParam::BINDS => [ $key => $id ] ] ) )
            {
                return ; // exist
            }
        }
        catch( Exception $exception )
        {
            throw new Error404( 'The ' . $name . ' reference can\'t be found, ' .  $exception->getMessage() ) ;
        }

        throw new Error404( 'The ' . $name . ' reference not exist with a invalid document : ' .  json_encode( $document , JSON_UNESCAPED_SLASHES ) ) ;
    }

    /**
     * Resolves a {@see DocumentsModel} instance, either directly or via the DI container.
     *
     * When a string is passed and the container holds a matching entry, the service is resolved.
     * The resulting value is returned only if it is a {@see DocumentsModel}; otherwise `null`.
     *
     * @param DocumentsModel|string|null $documents A ready model instance, a container service id, or `null`.
     *
     * @return DocumentsModel|null The resolved model, or `null` when it cannot be resolved.
     *
     * @throws DependencyException If the dependency cannot be resolved by the container.
     * @throws NotFoundException If no entry is found for the given identifier in the container.
     * @throws ContainerExceptionInterface If an error occurs while retrieving an entry from the dependency-injection container.
     * @throws NotFoundExceptionInterface If no entry is found for the requested identifier in the container.
     *
     * @example
     * ```php
     * // From a service id registered in the container
     * $model = $this->getDocumentsModel( ProductsModel::class );
     *
     * // From an existing instance (returned as-is)
     * $model = $this->getDocumentsModel( $productsModel );
     * ```
     */
    public function getDocumentsModel( DocumentsModel|string|null $documents ) : ?DocumentsModel
    {
        if( is_string( $documents ) && $this->container->has( $documents ) )
        {
            $documents = $this->container->get( $documents ) ;
        }
        return $documents instanceof DocumentsModel ? $documents : null ;
    }
}