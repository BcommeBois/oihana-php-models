<?php

namespace oihana\models\traits\alters;

trait AlterJSONStringifyPropertyTrait
{
    /**
     * Returns the JSON representation of a value
     * @param mixed $value
     * @param array $definition
     * @param bool $modified
     * @return string|false|null
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