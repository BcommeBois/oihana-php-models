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
 * @package oihana\models\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait DocumentsTrait
{
    use ContainerTrait ;

    /**
     * Assert the existence of a specific property value in a Document model.
     * @param object|string|int|null $document The document to validate.
     * @param ExistModel $model The OpenEdge model reference.
     * @param string $name The optional name of the resource to validate (use it in the error message).
     * @param string|null $key
     * @return void
     * @throws Error404
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
     * Returns a Documents Model instance directly or with the DI container.
     * @param DocumentsModel|string|null $documents
     * @return DocumentsModel|null
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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