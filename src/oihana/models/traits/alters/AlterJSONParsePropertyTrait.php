<?php

namespace oihana\models\traits\alters;

trait AlterJSONParsePropertyTrait
{
    /**
     * Decodes a JSON string
     * @param mixed $value
     * @param array $definition
     * @param bool $modified
     * @return string|false|null
     */
    public function alterJsonParseProperty( mixed $value , array $definition = [] , bool &$modified = false ) :mixed
    {
        if( is_string( $value ) && json_validate( $value ) )
        {
            $args = [ $value , ...$definition ]; ;
            $modified = true ;
            return json_decode( ...$args ) ?? null ;
        }
        return $value ;
    }
}