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
 * Mix this trait into a model (typically a PDO-backed one) when you want the bind variables
 * passed to a prepared statement to be normalized/cast before execution — for example casting
 * incoming string identifiers to `int`, prices to `float`, or hydrating nested documents.
 * The alteration rules are declared once in {@see $bindAlters} and may be scoped per context.
 *
 * @package oihana\models\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait AlterBindVarsTrait
{
    use AlterTrait ,
        ContainerTrait ;

    /**
     * The alteration definitions applied to bind variables.
     *
     * Keys are either property names mapping to an {@see Alter} definition, or context names
     * mapping to a nested array of per-property definitions (selected through the `$context`
     * argument of {@see alterBindVars()}).
     *
     * @var array
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
     * @throws ContainerExceptionInterface If an error occurs while retrieving an entry from the dependency-injection container.
     * @throws DependencyException If the dependency cannot be resolved by the container.
     * @throws NotFoundException If no entry is found for the given identifier in the container.
     * @throws NotFoundExceptionInterface If no entry is found for the requested identifier in the container.
     * @throws ReflectionException If a class or property cannot be reflected (e.g. during hydration).
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
     * Initializes the `$bindAlters` property from an initialization array.
     *
     * The value is read from the {@see ModelParam::BINDS_ALTERS} key when present;
     * otherwise the current value of `$bindAlters` is kept.
     *
     * @param array $init Initialization options, typically the model constructor payload.
     *
     * @return static The current instance, for fluent chaining.
     */
    public function initializeBindVarsAlters( array $init = [] ):static
    {
        $this->bindAlters = $init[ ModelParam::BINDS_ALTERS ] ?? $this->bindAlters ;
        return $this ;
    }
}