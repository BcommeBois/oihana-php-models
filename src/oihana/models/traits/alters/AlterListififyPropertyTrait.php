<?php

namespace oihana\models\traits\alters;

use oihana\core\arrays\CleanFlag ;

use function oihana\core\normalize ;
use function oihana\core\strings\resolveList;

/**
 * Provides a method to transform a string or array into a normalized list string.
 *
 * This trait is typically used in alteration pipelines to convert semicolon-separated
 * strings or arrays into clean, formatted list strings with configurable separators.
 *
 * The transformation process:
 * - Splits strings by a separator (default: `;`)
 * - Trims each element
 * - Removes empty elements
 * - Joins elements with a replacement separator (default: `PHP_EOL`)
 * - Returns a default value if the result is empty
 *
 * The `$modified` flag is set to `true` if the resulting value differs from the original.
 *
 * @see resolveList For the underlying list resolution logic.
 *
 * @example
 * ```php
 * use oihana\models\traits\alters\AlterListifyPropertyTrait;
 *
 * class Product {
 * use AlterListifyPropertyTrait;
 * }
 *
 * $product = new Product();
 * $modified = false;
 *
 * // Default behavior (split by ';', join with PHP_EOL)
 * $value = $product->alterListifyProperty('foo;bar;baz', [], $modified);
 * // Result: "foo\nbar\nbaz"
 * // $modified === true
 *
 * // Custom separator and replacement
 * $value = $product->alterListifyProperty('a,b,c', [',', ' | '], $modified);
 * // Result: "a | b | c"
 * // $modified === true
 *
 * // With default fallback for empty input
 * $value = $product->alterListifyProperty(';;;', [';', PHP_EOL, 'N/A'], $modified);
 * // Result: "N/A"
 * // $modified === true
 *
 * // Array input
 * $value = $product->alterListifyProperty(['foo', '  bar  ', '', 'baz'], [], $modified);
 * // Result: "foo\nbar\nbaz"
 * // $modified === true
 * ```
 *
 * @package oihana\models\traits\alters
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait AlterListififyPropertyTrait
{
    /**
     * Transforms a string or array into a normalized list string.
     *
     * The transformation can be customized via the `$definition` array:
     * - `$definition[0]` (string): Input separator for strings (default: `;`)
     * - `$definition[1]` (string): Output separator for joining (default: `PHP_EOL`)
     * - `$definition[2]` (string|null): Default value if result is empty (default: `null`)
     *
     * @param mixed $value The value to transform (string, array, or null)
     * @param array $definition Optional parameters: [separator, replace, default]
     * @param bool $modified Reference flag indicating if the value was modified
     *
     * @return string|null The normalized list string, or default if empty
     *
     * @example
     * ```php
     * // Use default separators
     * $this->alterListifyProperty('a;b;c');
     * // Result: "a\nb\nc"
     *
     * // Custom separators
     * $this->alterListifyProperty('a,b,c', [',', ' - ']);
     * // Result: "a - b - c"
     *
     * // With fallback default
     * $this->alterListifyProperty('', [';', PHP_EOL, 'empty']);
     * // Result: "empty"
     * ```
     */
    public function alterListifyProperty
    (
        mixed $value ,
        array $definition = [] ,
        bool  &$modified  = false
    )
    : mixed
    {
        $separator = $definition[0] ?? ';';
        $replace   = $definition[1] ?? PHP_EOL;
        $default   = $definition[2] ?? null;

        $newValue = resolveList( $value , $separator , $replace , $default ) ;

        if ( $newValue !== $value )
        {
            $modified = true ;
        }

        return $newValue ;
    }
}