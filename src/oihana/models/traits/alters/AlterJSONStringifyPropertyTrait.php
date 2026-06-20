<?php

namespace oihana\models\traits\alters;

/**
 * Encodes a property value into its JSON string representation.
 *
 * This alteration is declared with the `Alter::JSON_STRINGIFY` type. It is the counterpart
 * of {@see AlterJSONParsePropertyTrait} and is used in property transformation pipelines to
 * serialize an array or object into a JSON string before it is persisted or sent over the
 * wire:
 *
 * ```php
 * Property::METADATA => Alter::JSON_STRINGIFY ,
 * ```
 *
 * The extra elements of the alter definition are forwarded as the remaining arguments of
 * {@see json_encode()} (`flags`, `depth`), allowing options such as `JSON_PRETTY_PRINT` or
 * `JSON_UNESCAPED_UNICODE` to be configured per property.
 *
 * The `$modified` flag is always set to `true` since the encoding pass is unconditional.
 *
 * @package oihana\models\traits\alters
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait AlterJSONStringifyPropertyTrait
{
    /**
     * Encodes a value into its JSON string representation.
     *
     * The value is always passed to {@see json_encode()}; the optional `$definition` entries
     * are forwarded as the following arguments (`flags`, `depth`). A `false` result from
     * `json_encode()` is normalized to `null`.
     *
     * @param mixed $value      The value to encode (array, object, scalar, etc.).
     * @param array $definition Extra arguments forwarded to {@see json_encode()}:
     *                          `[ flags , depth ]` (e.g. `JSON_PRETTY_PRINT`). Empty by default.
     * @param bool  $modified   Reference flag; always set to `true`.
     *
     * @return string|null|false The JSON string, or `null` when encoding fails.
     *
     * @example
     * ```php
     * use oihana\models\traits\alters\AlterJSONStringifyPropertyTrait;
     *
     * class Record
     * {
     *     use AlterJSONStringifyPropertyTrait;
     * }
     *
     * $record   = new Record();
     * $modified = false;
     *
     * $json = $record->alterJsonStringifyProperty( [ 'a' => 1 , 'b' => 2 ] , [] , $modified );
     * // $json     === '{"a":1,"b":2}'
     * // $modified === true
     *
     * // With pretty-print flag
     * $pretty = $record->alterJsonStringifyProperty( [ 'a' => 1 ] , [ JSON_PRETTY_PRINT ] , $modified );
     * // $pretty === "{\n    \"a\": 1\n}"
     * ```
     */
    public function alterJsonStringifyProperty( mixed $value , array $definition = [] , bool &$modified = false  ) : string|null|false
    {
        $args = [ $value ] ;
        if( count( $definition ) > 0 )
        {
            $args = array_merge( $args , $definition ) ;
        }
        $modified = true ;
        return json_encode( ...$args ) ?? null ;
    }
}