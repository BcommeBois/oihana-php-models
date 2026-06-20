<?php

namespace oihana\models\traits\alters;

use oihana\enums\Char;

trait AlterArrayCleanPropertyTrait
{
    /**
     * Clean an array of null or empty string elements.
     * @param mixed $value
     * @param bool $modified
     * @return array|float
     */
    public function alterArrayCleanProperty( mixed $value , bool &$modified = false ): array|float
    {
        if( is_array( $value ) )
        {
            $value = array_filter( $value , fn( $item ) => $item != Char::EMPTY && isset( $item )  ) ;
            $modified = true ;
        }
        return $value ;
    }
}