<?php

namespace oihana\models\traits;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;

use oihana\models\traits\alters\AlterListififyPropertyTrait;
use ReflectionException;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use oihana\models\enums\Alter;
use oihana\models\traits\alters\AlterArrayCleanPropertyTrait;
use oihana\models\traits\alters\AlterArrayPropertyTrait;
use oihana\models\traits\alters\AlterCallablePropertyTrait;
use oihana\models\traits\alters\AlterFloatPropertyTrait;
use oihana\models\traits\alters\AlterGetDocumentPropertyTrait;
use oihana\models\traits\alters\AlterHydratePropertyTrait;
use oihana\models\traits\alters\AlterIntPropertyTrait;
use oihana\models\traits\alters\AlterJSONParsePropertyTrait;
use oihana\models\traits\alters\AlterJSONStringifyPropertyTrait;
use oihana\models\traits\alters\AlterKeyTrait;
use oihana\models\traits\alters\AlterMapPropertyTrait;
use oihana\models\traits\alters\AlterNormalizePropertyTrait;
use oihana\models\traits\alters\AlterNotPropertyTrait;
use oihana\models\traits\alters\AlterUrlPropertyTrait;
use oihana\models\traits\alters\AlterValueTrait;

use function oihana\core\accessors\getKeyValue;
use function oihana\core\accessors\setKeyValue;
use function oihana\core\arrays\toArray;

/**
 * Provides a set of methods to alter properties of arrays or objects based on a configurable set of rules (called "alters").
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
trait AlterTrait
{
    use AlterKeyTrait ,
        AlterArrayPropertyTrait ,
        AlterArrayCleanPropertyTrait ,
        AlterCallablePropertyTrait ,
        AlterFloatPropertyTrait ,
        AlterGetDocumentPropertyTrait ,
        AlterHydratePropertyTrait ,
        AlterIntPropertyTrait ,
        AlterJSONParsePropertyTrait ,
        AlterJSONStringifyPropertyTrait ,
        AlterListififyPropertyTrait ,
        AlterMapPropertyTrait ,
        AlterNormalizePropertyTrait ,
        AlterNotPropertyTrait ,
        AlterUrlPropertyTrait ,
        AlterValueTrait ;

    /**
     * Alters a specific property of the given document using one or more transformation rules.
     *
     * The transformation is defined by the `$definition` argument, which can be:
     * - A string representing a single `Alter::` constant (e.g. `Alter::FLOAT`)
     * - An array with a single alteration and parameters: `[ Alter::URL , '/product/' ]`
     * - An array with chained alterations (NEW): `[ Alter::NORMALIZE , [ Alter::HYDRATE , Class::class ] ]`
     * - An array with multiple simple alterations (NEW): `[ Alter::TRIM , Alter::UPPERCASE , Alter::NORMALIZE ]`
     *
     * If the alteration modifies the value, the altered value is set back into the document.
     * Otherwise, the original document is returned unmodified.
     *
     * Supported alter types:
     * - Alter::ARRAY          — Explodes a string into an array (using `;`) and applies sub-alters
     * - Alter::CALL           — Calls a user-defined callable on the value
     * - Alter::CLEAN          — Removes empty (`""`) or null elements from arrays
     * - Alter::FLOAT          — Casts the value to float
     * - Alter::GET            — Resolves a document by ID using a model
     * - Alter::HYDRATE        — Hydrate a value with a specific class.
     * - Alter::INT            — Casts the value to integer
     * - Alter::JSON_PARSE     — Parses a JSON string into a PHP value
     * - Alter::JSON_STRINGIFY — Encodes a value into a JSON string
     * - Alter::MAP            — Normalize a document property using configurable flags
     * - Alter::NORMALIZE      — Normalize a document property using configurable flags
     * - Alter::NOT            — Invert boolean values
     * - Alter::URL            — Generates a URL based on document properties
     * - Alter::VALUE          — Replaces the value with a fixed constant
     *
     * @param string       $key        The name of the property to alter (e.g. 'price', 'tags')
     * @param array|object $document   The document (array or object) passed by reference
     * @param string|array $definition The alteration definition: either a string (`Alter::`) or an array (`[ Alter::X , ...args ]`)
     * @param ?Container   $container  An optional DI container reference.
     *
     * @return array|object The altered document (same reference type as input)
     *
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     *
     * @example
     * ```php
     * $this->alters =
     * [
     *     'price'    => Alter::FLOAT,                         // Casts 'price' to float
     *     'tags'     => [ Alter::ARRAY , Alter::CLEAN ],      // Splits and cleans 'tags'
     *     'meta'     => [ Alter::JSON_PARSE ],                // Parses 'meta' JSON string
     *     'url'      => [ Alter::URL , '/product/' ],         // Generates a product URL
     *     'discount' => [ Alter::CALL , fn($v) => $v * 0.9 ], // Applies a callable
     *     'rating'   => [ Alter::VALUE , 5 ],                 // Fixed value replacement
     *     'geo'      => [ Alter::NORMALIZE , [ Alter::HYDRATE , GeoCoordinates::class ] ],
     *     'name'     => [ Alter::TRIM , Alter::UPPERCASE , Alter::NORMALIZE ],
     * ];
     *
     * $document =
     * [
     *     'price'    => '29.90',
     *     'tags'     => 'a;;b;',
     *     'meta'     => '{"active":true}',
     *     'url'      => '123',
     *     'discount' => 100,
     *     'rating'   => 0,
     *     'geo'      => ['latitude' => null, 'longitude' => 2.5],
     *     'name'     => '  john doe  ',
     * ];
     *
     * $result = $this->alterProperty('price', $document, Alter::FLOAT);
     * // Returns the document with 'price' casted to float (29.9)
     * ```
     */
    protected function alterProperty
    (
        string       $key ,
        array|object $document ,
        string|array $definition ,
        ?Container   $container   = null ,
    )
    : array|object
    {
        $definitions = toArray( $definition ) ;
        return $this->isChainedDefinition( $definitions )
            ? $this->applyChainedAlterations ( $key , $document , $definitions , $container )
            : $this->applySingleAlteration   ( $key , $document , $definitions , $container ) ;
    }

    /**
     * Applies chained alterations to a property.
     *
     * Each alteration in the chain is applied sequentially, with the output of one
     * becoming the input of the next.
     *
     * @param string       $key         The property key
     * @param array|object $document    The document containing the property
     * @param array        $definitions The array of alteration definitions
     * @param ?Container   $container   An optional DI container reference.
     *
     * @return array|object $document The document with the altered property
     *
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    protected function applyChainedAlterations
    (
        string       $key ,
        array|object $document ,
        array        $definitions ,
        ?Container   $container   = null ,
    )
    : array|object
    {
        $value          = getKeyValue( document: $document , key: $key );
        $globalModified = false;

        foreach ( $definitions as $definition )
        {
            $modified = false;

            // Normalize each step of the chain
            if ( is_array( $definition ) && count( $definition ) > 0 )
            {
                $alter = array_shift( $definition );
                $params = $definition;
            }
            else
            {
                $alter = $definition;
                $params = [];
            }

            $value = $this->executeAlteration
            (
                $alter       ,
                $key         ,
                $value       ,
                $params      ,
                $document    ,
                $container   ,
                $modified
            );

            if ( $modified )
            {
                $globalModified = true;
            }
        }

        return $globalModified ? setKeyValue( $document , $key , $value ) : $document ;
    }

    /**
     * Applies a single alteration (original behavior).
     *
     * @param string       $key         The property key
     * @param array|object $document    The document containing the property
     * @param array        $definitions The alteration definition with parameters
     * @param ?Container   $container   An optional DI container reference.
     *
     * @return array|object The document with the altered property
     *
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    protected function applySingleAlteration
    (
        string       $key ,
        array|object $document ,
        array        $definitions ,
        ?Container   $container   = null ,
    )
    : array|object
    {
        $alter    = array_shift( $definitions );
        $params   = $definitions;
        $value    = getKeyValue( document: $document , key: $key );
        $modified = false;

        $value = $this->executeAlteration
        (
            $alter     ,
            $key       ,
            $value     ,
            $params    ,
            $document  ,
            $container ,
            $modified
        );

        return $modified ? setKeyValue( $document , $key , $value ) : $document;
    }

    /**
     * Executes a specific alteration.
     *
     * @param string|Alter $alter     The alteration type
     * @param mixed        $value     The value to alter
     * @param array        $params    The alteration parameters
     * @param string       $key       The property key (for context)
     * @param array|object $document  The full document (for context)
     * @param ?Container   $container An optional DI container reference.
     * @param bool         $modified  Output parameter indicating if the value was modified
     *
     * @return mixed The altered value
     *
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    protected function executeAlteration
    (
        string|Alter $alter ,
        string       $key ,
        mixed        $value ,
        array        $params ,
        array|object &$document ,
        ?Container   $container = null ,
        bool         &$modified = false ,
    )
    : mixed
    {
        return match ( $alter )
        {
            Alter::ARRAY          => $this->alterArrayProperty         ( $value    , $params , $container , $modified ) ,
            Alter::CALL           => $this->alterCallableProperty      ( $value    , $params , $modified ) ,
            Alter::CLEAN          => $this->alterArrayCleanProperty    ( $value    , $modified ),
            Alter::FLOAT          => $this->alterFloatProperty         ( $value    , $modified ),
            Alter::GET            => $this->alterGetDocument           ( $value    , $params ,  $container , $modified ) ,
            Alter::HYDRATE        => $this->alterHydrateProperty       ( $value    , $params ,  $modified ) ,
            Alter::INT            => $this->alterIntProperty           ( $value    , $modified ) ,
            Alter::JSON_PARSE     => $this->alterJsonParseProperty     ( $value    , $params , $modified ) ,
            Alter::JSON_STRINGIFY => $this->alterJsonStringifyProperty ( $value    , $params , $modified ),
            Alter::LISTIFY        => $this->alterListifyProperty       ( $value    , $params , $modified ),
            Alter::MAP            => $this->alterMapProperty           ( $document , $container , $key , $value , $params , $modified ),
            Alter::NORMALIZE      => $this->alterNormalizeProperty     ( $value    , $params , $modified ),
            Alter::NOT            => $this->alterNotProperty           ( $value    , $modified ),
            Alter::URL            => $this->alterUrlProperty           ( $document , $params , $container , $modified ) ,
            Alter::VALUE          => $this->alterValue                 ( $value    , $params , $modified ) ,
            default               => $value
        };
    }

    /**
     * Checks if a value is an Alter enum or an array starting with an Alter enum.
     *
     * @param mixed $first The value to check
     * @return bool True if it's an Alter or array with Alter as first element
     */
    protected function firstIsAlter( mixed $first ): bool
    {
        return Alter::includes( $first ) ||
            ( is_array( $first ) && count( $first ) > 0 && Alter::includes( $first[0] ) );
    }

    /**
     * Detects if the definition represents chained alterations.
     *
     * Chaining is detected when:
     * - The first element is an Alter enum
     * - AND the second element is either:
     *   - Another Alter enum (simple chaining)
     *   - An array whose first element is an Alter enum (chaining with params)
     *
     * @param array $definitions The alteration definitions
     *
     * @return bool True if chaining is detected, false otherwise
     */
    protected function isChainedDefinition( array $definitions ): bool
    {
        if ( count( $definitions ) < 2 )
        {
            return false;
        }

        $first  = $definitions[0] ?? null ;
        $second = $definitions[1] ?? null ;

        if ( ! $this->firstIsAlter( $first ) )
        {
            return false;
        }

        // Second must be either an Alter or an array starting with an Alter
        if ( Alter::includes( $second ) )
        {
            return true;
        }

        if ( is_array( $second ) && count( $second ) > 0 )
        {
            return Alter::includes( $second[0] ) ;
        }

        return false ;
    }
}