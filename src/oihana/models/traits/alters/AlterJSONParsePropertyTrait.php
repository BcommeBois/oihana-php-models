<?php

namespace oihana\models\traits\alters;

/**
 * Decodes a JSON string property into its native PHP representation.
 *
 * This alteration is declared with the `Alter::JSON_PARSE` type. It is used in property
 * transformation pipelines to turn a JSON-encoded string (often coming from a database
 * column or an API payload) back into an object, array or scalar before the value is used:
 *
 * ```php
 * Property::METADATA => Alter::JSON_PARSE ,
 * ```
 *
 * Only valid JSON strings are decoded (validated with {@see json_validate()}); any other
 * value — including malformed JSON and non-string values — is returned unchanged. The extra
 * elements of the alter definition are forwarded as the remaining arguments of
 * {@see json_decode()} (`associative`, `depth`, `flags`), so the decoding mode is fully
 * configurable.
 *
 * The `$modified` flag is set to `true` only when an actual JSON decode is performed.
 *
 * @package oihana\models\traits\alters
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait AlterJSONParsePropertyTrait
{
    /**
     * Decodes a JSON-encoded string into its native PHP value.
     *
     * The value is decoded only when it is a string that passes {@see json_validate()};
     * otherwise it is returned as-is. The optional `$definition` entries are passed straight
     * to {@see json_decode()} after the value (`associative`, `depth`, `flags`).
     *
     * @param mixed $value      The value to decode. Decoded only when it is a valid JSON
     *                          string; any other value is returned unchanged.
     * @param array $definition Extra arguments forwarded to {@see json_decode()}:
     *                          `[ associative , depth , flags ]`. Defaults to standard
     *                          `json_decode()` behavior (objects for JSON objects).
     * @param bool  $modified   Reference flag set to `true` when the value was decoded.
     *
     * @return mixed The decoded value (object, array, scalar or `null`), or the original
     *               value when it was not a valid JSON string.
     *
     * @example
     * ```php
     * use oihana\models\traits\alters\AlterJSONParsePropertyTrait;
     *
     * class Record
     * {
     *     use AlterJSONParsePropertyTrait;
     * }
     *
     * $record   = new Record();
     * $modified = false;
     *
     * // Decode to an associative array
     * $meta = $record->alterJsonParseProperty( '{"a":1,"b":2}' , [ true ] , $modified );
     * // $meta     === [ 'a' => 1 , 'b' => 2 ]
     * // $modified === true
     *
     * // Non-JSON input is left untouched
     * $plain = $record->alterJsonParseProperty( 'hello' , [] , $modified );
     * // $plain === 'hello'
     * ```
     */
    public function alterJsonParseProperty( mixed $value , array $definition = [] , bool &$modified = false ) :mixed
    {
        if( is_string( $value ) && json_validate( $value ) )
        {
            $args = [ $value , ...$definition ] ;
            $modified = true ;
            return json_decode( ...$args ) ?? null ;
        }
        return $value ;
    }
}