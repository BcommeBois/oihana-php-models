<?php

namespace oihana\models\enums;

use oihana\reflect\traits\ConstantsTrait;

/**
 * Enumeration of all transformation or filter operations that can be applied
 * to an object property or an array key.
 *
 * Each constant represents a type of alteration that can be performed
 * when normalizing or processing data within models or collections.
 *
 * Examples of usage include:
 * - Converting a value to a specific type (`INT`, `FLOAT`)
 * - Cleaning or normalizing arrays or strings (`CLEAN`, `NORMALIZE`)
 * - Parsing or serializing JSON (`JSON_PARSE`, `JSON_STRINGIFY`)
 * - Applying custom callbacks (`CALL`) or map (`MAP`)
 * - Extracting values via getters (`GET`)
 * - Handling URLs or array transformations (`URL`, `ARRAY`)
 *
 * Always reference these constants instead of the raw string so that an alter
 * pipeline keeps working if a value ever changes (no *magic strings*).
 *
 * Example:
 * ```php
 * use oihana\models\enums\Alter;
 *
 * // An alter definition mapping properties to the operation to apply.
 * $alters =
 * [
 *     'price'    => [ Alter::FLOAT ] ,
 *     'quantity' => [ Alter::INT ] ,
 *     'tags'     => [ Alter::JSON_PARSE ] ,
 *     'avatar'   => [ Alter::URL ] ,
 * ];
 * ```
 *
 * @package oihana\models\enums
 * @author  Marc Alcaraz
 * @since   1.0.0
 *
 * @see \oihana\models\traits\AlterDocumentTrait For usage of alter rules in models.
 */
class Alter
{
    use ConstantsTrait ;

    /**
     * Wraps the value into an array (or normalizes it to an array form).
     */
    public const string ARRAY = 'array' ;

    /**
     * Applies a user-supplied callback to the value.
     */
    public const string CALL = 'call' ;

    /**
     * Cleans the value (e.g. trims/strips empty entries).
     */
    public const string CLEAN = 'clean' ;

    /**
     * Casts the value to a floating-point number.
     */
    public const string FLOAT = 'float' ;

    /**
     * Extracts a value through a getter.
     */
    public const string GET = 'get' ;

    /**
     * Hydrates the value into a typed object/model instance.
     */
    public const string HYDRATE = 'hydrate' ;

    /**
     * Treats the value as a list (collection of items).
     */
    public const string LIST = 'list' ;

    /**
     * Casts the value to an integer.
     */
    public const string INT = 'int' ;

    /**
     * Decodes a JSON string into its PHP value.
     */
    public const string JSON_PARSE = 'jsonParse' ;

    /**
     * Encodes the value into a JSON string.
     */
    public const string JSON_STRINGIFY = 'jsonStringify' ;

    /**
     * Coerces the value into a list, wrapping a single item when needed.
     */
    public const string LISTIFY = 'listify' ;

    /**
     * Maps each element of the value through a transformation.
     */
    public const string MAP = 'map' ;

    /**
     * Normalizes the value (e.g. canonical form, whitespace/casing).
     */
    public const string NORMALIZE = 'normalize' ;

    /**
     * Negates the value (logical/boolean inversion).
     */
    public const string NOT = 'not' ;

    /**
     * Resolves the value into a document/resource URL.
     */
    public const string URL = 'url' ;

    /**
     * Replaces the value with a fixed/computed value.
     */
    public const string VALUE = 'value' ;
}