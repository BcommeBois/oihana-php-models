<?php

namespace oihana\models\traits\alters;

/**
 * Provides an alteration to invert boolean values.
 *
 * This trait defines a single alteration type `Alter::NOT` which will invert a boolean value
 * or an array of boolean values. Non-boolean values will be cast to boolean before inversion.
 *
 * Example usage in `AlterDocumentTrait`:
 * ```php
 * $this->alters = [
 *     'active' => Alter::NOT,          // single boolean
 *     'flags'  => Alter::NOT,          // array of booleans
 * ];
 *
 * $data = [
 *     'active' => true,
 *     'flags'  => [true, false, true],
 * ];
 *
 * $result = $this->alter($data);
 * // $result = [
 * //     'active' => false,
 * //     'flags'  => [false, true, false],
 * // ]
 * ```
 *
 * Supported input types:
 * - `bool`           → returns the opposite boolean.
 * - `array<bool>`    → returns a new array with all elements inverted.
 * - any other value  → cast to boolean and inverted.
 *
 * @package oihana\models\traits\alters
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait AlterNotPropertyTrait
{
    /**
     * Invert a boolean value or an array of booleans.
     *
     * @param mixed $value    The value to alter. Can be a boolean, array of booleans, or any other value.
     * @param bool  $modified Reference flag set to true if the value is altered.
     *
     * @return bool|array Returns the inverted boolean or array of inverted booleans.
     *
     * @example
     * ```php
     * $processor = new class {
     * use \oihana\traits\alters\AlterNotPropertyTrait;
     * };
     *
     * // Single boolean
     * $modified = false;
     * $result = $processor->alterNotProperty(true, $modified);
     * // $result === false
     * // $modified === true
     *
     * // Array of booleans
     * $array = [true, false, true];
     * $result = $processor->alterNotProperty($array, $modified);
     * // $result === [false, true, false]
     * // $modified === true
     * ```
     */
    public function alterNotProperty( mixed $value , bool &$modified = false ): bool|array
    {
        $modified = true ;

        if ( is_array( $value ) )
        {
            return array_map( fn($v) => !$v, $value ) ;
        }

        return !$value ;
    }
}