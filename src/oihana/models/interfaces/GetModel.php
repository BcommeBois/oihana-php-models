<?php

namespace oihana\models\interfaces;

/**
 * Returns a specific document in the model.
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface GetModel extends ExistModel
{
    /**
     * Returns an item in the model.
     * @param array $init
     * @return mixed
     */
    public function get( array $init = [] ) :mixed ;
}