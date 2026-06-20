<?php

namespace oihana\models\enums\traits;

/**
 * The set of constant keys recognized in a model's `$init` options array.
 *
 * This trait holds every common parameter name shared by the model operations
 * and factories — covering identity (`id`, `key`, `keys`), payloads (`document`
 * via `model`), querying (`conditions`, `query`, `binds`, `sort`), caching
 * (`cache`, `ttl`), alteration (`alters`, `alterKey`) and behavioural toggles
 * (`debug`, `mock`, `throwable`, …).
 *
 * It is consumed by {@see \oihana\models\enums\ModelParam}, which combines it
 * with {@see \oihana\reflect\traits\ConstantsTrait} to expose the reflection
 * helpers. Reference these constants rather than raw strings to keep option
 * arrays consistent across the library (no *magic strings*).
 *
 * @package oihana\models\enums\traits
 * @author  Marc Alcaraz
 * @since   1.0.0
 */
trait ModelParamTrait
{
    /**
     * The 'alterKey' parameter.
     * Defines the default 'alter' key identifier.
     */
    public const string ALTER_KEY = 'alterKey' ;

    /**
     * The 'alters' parameter.
     */
    public const string ALTERS = 'alters' ;

    /**
     * The 'binds' parameter.
     */
    public const string BINDS = 'binds' ;

    /**
     * The 'bindsAlters' parameter.
     */
    public const string BINDS_ALTERS = 'bindsAlters' ;

    /**
     * The 'cache' parameter.
     */
    public const string CACHE = 'cache' ;

    /**
     * The 'conditions' parameter.
     */
    public const string CONDITIONS = 'conditions' ;

    /**
     * The 'debug' parameter.
     */
    public const string DEBUG = 'debug' ;

    /**
     * The 'default' parameter.
     */
    public const string DEFAULT = 'default' ;

    /**
     * The 'deferAssignment' parameter.
     */
    public const string DEFER_ASSIGNMENT = 'deferAssignment' ;

    /**
     * The 'enforce' parameter.
     */
    public const string ENFORCE = 'enforce' ;

    /**
     * The 'ensure' parameter.
     */
    public const string ENSURE = 'ensure' ;

    /**
     * The 'id' parameter.
     */
    public const string ID = 'id' ;

    /**
     * The 'key' parameter.
     */
    public const string KEY = 'key' ;

    /**
     * The 'keys' parameter.
     */
    public const string KEYS = 'keys' ;

    /**
     * The 'list' parameter.
     */
    public const string LIST = 'list' ;

    /**
     * The 'loggable' parameter.
     */
    public const string LOGGABLE = 'loggable' ;

    /**
     * The 'logger' parameter.
     */
    public const string LOGGER = 'logger' ;

    /**
     * The 'mock' parameter.
     */
    public const string MOCK = 'mock' ;

    /**
     * The 'model' parameter.
     */
    public const string MODEL = 'model' ;

    /**
     * The 'optimized' parameter.
     */
    public const string OPTIMIZED = 'optimized' ;

    /**
     * The 'owner' parameter.
     */
    public const string OWNER = 'owner' ;

    /**
     * The 'pdo' parameter.
     */
    public const string PDO = 'pdo' ;

    /**
     * The 'query' parameter.
     */
    public const string QUERY = 'query' ;

    /**
     * The 'queryBuilder' parameter.
     */
    public const string QUERY_BUILDER = 'queryBuilder' ;

    /**
     * The 'queryFields' parameter.
     */
    public const string QUERY_FIELDS = 'queryFields' ;

    /**
     * The 'queryId' parameter.
     */
    public const string QUERY_ID = 'queryId' ;

    /**
     * The 'schema' parameter.
     */
    public const string SCHEMA = 'schema' ;

    /**
     * The 'sort' parameter.
     */
    public const string SORT = 'sort' ;

    /**
     * The 'sortDefault' parameter.
     */
    public const string SORT_DEFAULT = 'sortDefault' ;

    /**
     * The 'throwable' parameter.
     */
    public const string THROWABLE = 'throwable' ;

    /**
     * The 'ttl' parameter.
     */
    public const string TTL = 'ttl' ;

    /**
     * The 'value' parameter.
     */
    public const string VALUE = 'value' ;
}