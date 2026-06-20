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
     * The default array definition to ensure attributes in the documents of the model.
     * @var ?array
     */
    public ?array $ensure = null ;

    /**
     * Ensures that specific attributes (keys or properties) exist on a document or a collection of documents.
     *
     * It parses the configuration from $init[ModelParam::ENSURE] and applies it to $data.
     * It automatically detects if $data is a collection (indexed array) or a single item.
     *
     * @param mixed &$data Reference to the document(s). Modified in place.
     * @param array $init  The initialization options containing ModelParam::ENSURE.
     *
     * @return void
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