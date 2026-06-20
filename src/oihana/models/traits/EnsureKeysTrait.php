<?php

namespace oihana\models\traits;

use oihana\models\enums\ModelParam;

use function oihana\core\accessors\ensureKeyValue;
use function oihana\core\arrays\isIndexed;

/**
 * Provides functionality to guarantee the existence of specific keys or properties
 * within document structures or collections.
 *
 * This trait processes the configuration provided in `ModelParam::ENSURE`
 * to automatically populate missing keys with default values.
 *
 * ### Usage example:
 *
 * ```php
 * class MyModel
 * {
 *    use EnsureKeysTrait;
 *
 *    public function fetchAndProcess($init)
 *    {
 *        $data = ['id' => 1];
 *
 *        // Ensures 'status' exists, defaults to 'draft'
 *        $this->ensureDocumentKeys($data, $init);
 *
 *        return $data;
 *     }
 * }
 *
 * // Usage
 * $model->fetchAndProcess
 * ([
 *     ModelParam::ENSURE  =>
 *     [
 *         ModelParam::KEYS    => ['status'],
 *         ModelParam::DEFAULT => 'draft'
 *     ]
 * ]);
 * ```
 *
 * @package oihana\models\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait EnsureKeysTrait
{
    /**
     * The default configuration used to ensure attributes on the model's documents.
     *
     * Either a flat list of keys, or an associative array with the {@see ModelParam::KEYS},
     * {@see ModelParam::DEFAULT} and {@see ModelParam::ENFORCE} entries. `null` means "no default".
     *
     * @var ?array
     */
    public ?array $ensure = null ;

    /**
     * Ensures that specific attributes (keys or properties) exist on a document or a collection.
     *
     * The configuration is resolved from `$init[ModelParam::ENSURE]`, falling back to the instance
     * `$ensure` property. It can be a flat list of keys or a structured array exposing
     * {@see ModelParam::KEYS}, {@see ModelParam::DEFAULT} and {@see ModelParam::ENFORCE}. The method
     * auto-detects whether `$data` is an indexed collection (each element is processed) or a single
     * document, and mutates it in place. It is a no-op when `$data` is empty or no configuration applies.
     *
     * @param mixed &$data Reference to the document or list of documents. Modified in place.
     * @param array  $init Runtime options; a `ModelParam::ENSURE` entry overrides the `$ensure` property.
     *
     * @return void
     *
     * @example
     * ```php
     * $data = [ 'id' => 1 ];
     * $this->ensureDocumentKeys( $data ,
     * [
     *     ModelParam::ENSURE =>
     *     [
     *         ModelParam::KEYS    => [ 'status' ] ,
     *         ModelParam::DEFAULT => 'draft' ,
     *     ]
     * ]);
     * // $data === [ 'id' => 1 , 'status' => 'draft' ]
     * ```
     */
    protected function ensureDocumentKeys( mixed &$data , array $init = [] ): void
    {
        // 1. Guard Clause : No data
        if ( empty( $data ) )
        {
            return ;
        }

        // 2. Resolve Configuration (Runtime vs Instance Property)
        $config = $init[ ModelParam::ENSURE ] ?? $this->ensure ;

        if ( !isset( $config ) )
        {
            return ;
        }

        // 3. Guard Clause : No configuration found
        if ( isset( $config[ ModelParam::KEYS ] ) )
        {
            $keys    = $config[ ModelParam::KEYS    ] ;
            $default = $config[ ModelParam::DEFAULT ] ?? null  ;
            $enforce = $config[ ModelParam::ENFORCE ] ?? false ;
        }
        else
        {
            $keys    = $config ;
            $default = null    ;
            $enforce = false   ;
        }

        // 4. Application (Collection vs Single Document)

        if ( is_array( $data ) && isIndexed( $data ) )
        {
            foreach ( $data as &$document )
            {
                $document = ensureKeyValue
                (
                    document: $document ,
                    keys:     $keys     ,
                    default:  $default  ,
                    enforce:  $enforce
                );
            }
        }
        else
        {
            $data = ensureKeyValue
            (
                document : $data    ,
                keys     : $keys    ,
                default  : $default ,
                enforce  : $enforce
            ) ;
        }
    }

    /**
     * Initialize the `ensure` definition of the model.
     *
     * @param array<string, mixed> $init Optional initialization array.
     *
     * @return static Returns `$this` to allow method chaining.
     */
    public function initializeEnsure( array $init = [] ):static
    {
        $this->ensure = $init[ ModelParam::ENSURE ] ?? null ;
        return $this ;
    }
}