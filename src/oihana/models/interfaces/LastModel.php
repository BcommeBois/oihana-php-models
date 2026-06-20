<?php

namespace oihana\models\interfaces;

/**
 * Returns the last document in the model (by default 'modified').
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface LastModel extends ExistModel
{
    /**
     * Returns a the last document in the model.
     * @param array $init
     * @return mixed
     */
    public function last( array $init = [] ) :mixed ;
}