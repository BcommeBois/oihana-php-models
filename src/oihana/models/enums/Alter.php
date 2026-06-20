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
 * @package oihana\models\enums
 * @author  Marc Alcaraz
 * @since   1.0.0
 *
 * @see AlterDocumentTrait For usage of alter rules in models.
 */
class Alter
{
    use ConstantsTrait ;

    public const string ARRAY          = 'array' ;
    public const string CALL           = 'call' ;
    public const string CLEAN          = 'clean' ;
    public const string FLOAT          = 'float' ;
    public const string GET            = 'get' ;
    public const string HYDRATE        = 'hydrate' ;
    public const string LIST           = 'list' ;
    public const string INT            = 'int' ;
    public const string JSON_PARSE     = 'jsonParse' ;
    public const string JSON_STRINGIFY = 'jsonStringify' ;
    public const string LISTIFY        = 'listify' ;
    public const string MAP            = 'map' ;
    public const string NORMALIZE      = 'normalize' ;
    public const string NOT            = 'not' ;
    public const string URL            = 'url' ;
    public const string VALUE          = 'value' ;
}