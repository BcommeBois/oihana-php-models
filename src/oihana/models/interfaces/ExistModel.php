<?php

namespace oihana\models\interfaces;

/**
 * Indicates if a document exist in the model.
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface ExistModel
{
    /**
     * Indicates if the passed-in item exist.
     * @param array $init The optional setting definition.
     * @return bool True of the value exist in the model.
     */
    public function exist( array $init = []  ) :bool ;
}