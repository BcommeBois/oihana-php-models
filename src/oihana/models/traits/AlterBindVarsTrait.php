<?php

namespace oihana\models\traits;

use DI\DependencyException;
use DI\NotFoundException;

use oihana\core\arrays\CleanFlag;
use ReflectionException;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use oihana\models\enums\ModelParam;
use oihana\traits\ContainerTrait;

use function oihana\core\accessors\hasKeyValue;
use function oihana\core\arrays\clean;
use function oihana\core\arrays\isAssociative;

/**
 * Applies defined alterations to a bind variables array definition based on the configured `$bindAlters`.
 *
 * This method transforms the input `$bindVars` according to the alteration rules:
 * - If `$bindVars` is a **sequential array** (list of associative arrays), alterations are recursively applied to each element.
 * - If `$bindVars` is an **associative array**, only the keys defined in the selected `$alters` context are processed.
 * - Scalar values (string, int, float, bool, resource, null) are returned unchanged unless specifically targeted in `$bindAlters`.
 * - Supports **chained alterations**: if a key in `$alters` maps to an array of alters, each is applied in sequence,
 * with the output of one becoming the input for the next.
 *
 * The optional `$context` parameter allows selecting a specific subset of alterations defined under that context
 * in `$this->bindAlters`. If `$context` is null, the top-level alterations array is used.
 *
 * Example usage:
 * ```php
 * class BindVarsProcessor
 * {
 *     use AlterBindVarsTrait;
 *
 *     public function __construct()
 *     {
 *         $this->bindsAlters =
 *         [
 *             'get' =>
 *             [
 *                'id' => Alter::FLOAT,
 *             ]
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
 * @param mixed $document The array or object to transform.
 *                        If it's a list of objects/arrays, the alteration is recursively applied to each.
 *
 * @return mixed The altered version of the document (same type as input).
 *
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @package oihana\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait AlterBindVarsTrait
{
    use AlterTrait ,
        ContainerTrait ;

    /**
     * The enumeration of all definitions to alter on the array or object key/value properties.
     */
    public array $bindAlters = [] ;

    /**
     * Applies defined alterations to a bind variables array definition based on the configured `$bindAlters`.
     *
     * This method transforms the input `$bindVars` according to the alteration rules:
     * - If `$bindVars` is a **sequential array** (list of associative arrays), alterations are recursively applied to each element.
     * - If `$bindVars` is an **associative array**, only the keys defined in the selected `$alters` context are processed.
     * - Scalar values (string, int, float, bool, resource, null) are returned unchanged unless specifically targeted in `$bindAlters`.
     * - Supports **chained alterations**: if a key in `$alters` maps to an array of alters, each is applied in sequence,
     * with the output of one becoming the input for the next.
     *
     * The optional `$context` parameter allows selecting a specific subset of alterations defined under that context
     * in `$this->bindAlters`. If `$context` is null, the top-level alterations array is used.
     *
     * @param array|null  $bindVars The bind variables definition to transform. Should be an associative array or a list of associative arrays.
     * @param string|null $context  Optional context key to select a specific set of alterations from `$this->bindAlters`.
     * @param int         $flags    $flags A bitmask of `CleanFlag` values. Defaults to `CleanFlag::DEFAULT`.
     *
     * @return array|null The transformed bind variables array, preserving the input structure. Returns the original input if no alterations apply.
     *
     * @throws ContainerExceptionInterface If a DI container error occurs during alteration.
     * @throws DependencyException If a dependency cannot be resolved during alteration.
     * @throws NotFoundException If a container service is not found during alteration.
     * @throws NotFoundExceptionInterface If a container service is not found during alteration.
     * @throws ReflectionException If a reflection operation fails during alteration (e.g., Hydrate or Get).
     *
     * @example
     * ```php
     * class BindVarsProcessor
     * {
     * use AlterBindVarsTrait;
     *
     *     public function __construct()
     *     {
     *         $this->bindAlters =
     *         [
     *             'get' => [
     *                 'id'    => Alter::INT,
     *                 'price' => Alter::FLOAT,
     *             ],
     *         ];
     *    }
     * }
     *
     * $processor = new BindVarsProcessor();
     * $bindVars  = ['id' => '42', 'price' => '19.99'];
     * $result    = $processor->alterBindVars($bindVars, 'get');
     *
     * // $result:
     * // [
     * //     'id'    => 42,
     * //     'price' => 19.99,
     * // ]
     * ```
     */
    public function alterBindVars
    (
        ?array  $bindVars ,
        ?string $context  = null ,
        int     $flags    = CleanFlag::DEFAULT
    )
    :?array
    {
        if ( $bindVars === null )
        {
            return null ;
        }

        if ( empty( $bindVars ) || !isAssociative( $bindVars )  )
        {
            return clean( $bindVars , $flags ) ;
        }

        $alters ??= $this->bindAlters ;

        if( $context !== null && array_key_exists( $context , $alters ) )
        {
            $alters = $alters[ $context ] ;
        }

        if ( count( $alters ) === 0 )
        {
            return clean( $bindVars , $flags ) ;
        }

        foreach ( $alters as $key => $definition )
        {
            if ( hasKeyValue( $bindVars , $key ) )
            {
                $bindVars = $this->alterProperty( $key , $bindVars , $definition , $this->container ) ;
            }
        }

        return clean( $bindVars , $flags ) ;
    }

    /**
     * Initialize the 'bindAlters' property.
     * @param array $init
     * @return static
     */
    public function initializeBindVarsAlters( array $init = [] ):static
    {
        $this->bindAlters = $init[ ModelParam::BINDS_ALTERS ] ?? $this->bindAlters ;
        return $this ;
    }
}