<?php

namespace oihana\models\traits\alters;

/**
 * Provides a method to replace a value with a fixed new one if different.
 *
 * This trait is part of the alteration system and is intended to be used
 * in combination with {@see \oihana\models\traits\AlterDocumentTrait}.
 * It encapsulates the logic for the `Alter::VALUE` transformation type.
 *
 * Example usage:
 * ```php
 * use oihana\models\enums\Alter;
 * use oihana\models\traits\AlterDocumentTrait;
 * use oihana\models\traits\alters\AlterValueTrait;
 *
 * class Example
 * {
 *     use AlterDocumentTrait, AlterValueTrait;
 *
 *     public function __construct()
 *     {
 *         $this->alters =
 *         [
 *             'status' => [ Alter::VALUE , 'published' ] ,
 *         ];
 *     }
 * }
 *
 * $doc = [ 'status' => 'draft' ];
 *
 * $processor = new Example();
 * $result    = $processor->alter($doc);
 *
 * // Result:
 * // [
 * //     'status' => 'published'
 * // ]
 * ```
 *
 * @package oihana\models\traits\alters
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait AlterValueTrait
{
    /**
     * Replaces a value with a fixed replacement when they differ, otherwise keeps the original.
     *
     * The replacement is read from `$definition[0]`. When it is strictly equal (`!==`) to the
     * current value, nothing changes and `$modified` is left untouched; otherwise the new value
     * is returned and `$modified` is set to `true`.
     *
     * @param mixed $value      The original value.
     * @param array $definition The alter definition; `$definition[0]` holds the replacement value
     *                          (defaults to `null` when absent).
     * @param bool  $modified   Reference flag set to `true` when the value was actually replaced.
     *
     * @return mixed The replacement value when it differs from the original, otherwise the original.
     *
     * @example
     * ```php
     * use oihana\models\traits\alters\AlterValueTrait;
     *
     * class Example
     * {
     *     use AlterValueTrait;
     * }
     *
     * $example  = new Example();
     * $modified = false;
     *
     * $status = $example->alterValue( 'draft' , [ 'published' ] , $modified );
     * // $status   === 'published'
     * // $modified === true
     *
     * // Same value: no change
     * $same = $example->alterValue( 'published' , [ 'published' ] , $modified );
     * // $same === 'published'
     * ```
     */
    public function alterValue
    (
        mixed $value ,
        array $definition = [] ,
        bool  &$modified  = false
    )
    : mixed
    {
        $newValue = $definition[0] ?? null ;
        if( $value !== $newValue )
        {
            $modified = true ;
            return $newValue ;
        }
        return $value ;
    }
}