<?php

namespace oihana\models\traits;

use DI\DependencyException;
use DI\NotFoundException;

use ReflectionException;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use oihana\models\enums\ModelParam;
use oihana\traits\ContainerTrait;

use function oihana\core\accessors\hasKeyValue;
use function oihana\core\arrays\isAssociative;

/**
 * Provides a system to alter properties of arrays or objects based on a configurable set of rules (called "alters").
 *
 * The alteration definitions are stored in the `$alters` array and can apply various transformations to data,
 * such as converting values to floats, parsing JSON, cleaning arrays, or resolving URLs.
 *
 * Supports chaining multiple alterations on a single property:
 *
 * Example usage:
 * ```php
 * class MyProcessor
 * {
 *     use AlterDocumentTrait;
 *
 *     public function __construct()
 *     {
 *         $this->alters =
 *         [
 *             'price' => Alter::FLOAT,
 *             'tags'  => [ Alter::ARRAY , Alter::CLEAN ],
 *             'meta'  => [ Alter::JSON_PARSE ],
 *             'link'  => [ Alter::URL , '/product/' ],
 *             'score' => [ Alter::CALL , fn( $value ) => $value * 10 ],
 *             'total' => [ ALTER::MAP , fn( &$document ) => $document['price'] + ( $document['price'] * ( $document['vat'] ?? 0 ) ) ] ,
 *             'geo'   => [ Alter::NORMALIZE , [ Alter::HYDRATE , GeoCoordinates::class ] ],
 *             'name'  => [ Alter::TRIM , Alter::UPPERCASE , Alter::NORMALIZE ],
 *         ];
 *     }
 * }
 * ```
 *
 * Supported alteration types (see enum Alter):
 * - Alter::ARRAY           → Split string into array and apply sub-alters.
 * - Alter::CLEAN           → Remove empty/null elements from an array.
 * - Alter::CALL            → Call a function on the value.
 * - Alter::FLOAT           → Convert to float (or array of floats).
 * - Alter::GET             → Fetch a document using a model.
 * - Alter::HYDRATE         → Hydrate a value with a specific class.
 * - Alter::INT             → Convert to integer (or array of integers).
 * - Alter::JSON_PARSE      → Parse JSON string.
 * - Alter::JSON_STRINGIFY  → Convert value to JSON string.
 * - Alter::MAP             → Map a property of a document (or all the document structure) - Can transform or update the document.
 * - Alter::NORMALIZE       → Normalize a document property using configurable flags.
 * - Alter::NOT             → Invert boolean values.
 * - Alter::URL             → Generate a URL from a property.
 * - Alter::VALUE           → Override with a fixed value.
 *
 * @package oihana\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait AlterDocumentTrait
{
    use AlterTrait ,
        ContainerTrait ;

    /**
     * The enumeration of all definitions to alter on the array or object key/value properties.
     */
    public array $alters = [] ;

    /**
     * Applies defined alterations to a document (array or object) based on a set of rules.
     *
     * This method inspects the input document and applies transformations according to the provided `$alters` definitions:
     *
     * - If the document is a **sequential array** (list), the alteration is recursively applied to each element.
     * - If the document is an **associative array** or an **object**, only the keys defined in `$alters` are processed.
     * - If a key in `$alters` is associated with a **chained alteration** (array of alters), each alteration
     *   is applied sequentially, passing the output of one as input to the next.
     * - Scalar values (string, int, float, bool, resource, null) are returned unchanged unless specifically targeted in `$alters`.
     *
     * The `$alters` parameter allows temporarily overriding or extending the internal `$this->alters` property
     * for the current method call. Passing `null` will use the trait's internal `$this->alters` array.
     *
     * @param mixed       $document The input to transform. Can be an associative array, object, or a list of items.
     * @param array|null  $alters   Optional temporary alter definitions for this call. Keys are property names, values are `Alter::` constants or arrays of chained alters.
     *
     * @return mixed The transformed document, preserving the input structure (array, object, or list of arrays/objects).
     *
     * @throws ContainerExceptionInterface If a DI container error occurs during alteration.
     * @throws DependencyException If a dependency cannot be resolved during alteration.
     * @throws NotFoundException If a container service is not found during alteration.
     * @throws NotFoundExceptionInterface If a container service is not found during alteration.
     * @throws ReflectionException If a reflection operation fails during alteration (e.g., Hydrate or Get).
     *
     * @example
     * ```php
     * class Example
     * {
     *     use AlterDocumentTrait;
     *
     *     public function __construct()
     *     {
     *         $this->alters = [
     *             'price' => Alter::FLOAT,
     *             'tags'  => [ Alter::ARRAY, Alter::CLEAN ],
     *             'name'  => [ Alter::TRIM, Alter::UPPERCASE ], // Chained alterations
     *         ];
     *     }
     * }
     *
     * $input = [
     *     'price' => '19.99',
     *     'tags'  => 'foo,bar',
     *     'name'  => '  john  '
     * ];
     *
     * $processor = new Example();
     * $output = $processor->alter($input);
     *
     * // $output:
     * // [
     * //     'price' => 19.99,
     * //     'tags'  => ['foo', 'bar'],
     * //     'name'  => 'JOHN',
     * // ]
     * ```
     */
    public function alter( mixed $document , ?array $alters = null ) :mixed
    {
        if ( !is_array( $document ) && !is_object( $document ) )
        {
            return $document ;
        }

        $alters ??= $this->alters ;

        if ( count( $alters ) === 0 )
        {
            return $document ;
        }

        if ( is_array( $document ) && !isAssociative( $document ) )
        {
            return array_map( fn( $value ) => $this->alter( $value ) , $document ) ;
        }

        foreach ( $alters as $key => $definition )
        {
            if ( hasKeyValue( $document , $key ) )
            {
                $document = $this->alterProperty( $key , $document , $definition , $this->container ) ;
            }
        }

        return $document ;
    }

    /**
     * Initialize the 'alters' property.
     * @param array $init
     * @return static
     */
    public function initializeAlters( array $init = [] ):static
    {
        $this->alters = $init[ ModelParam::ALTERS ] ?? $this->alters ;
        return $this->initializeAlterKey( $init ) ;
    }
}