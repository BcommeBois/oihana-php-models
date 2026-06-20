<?php

namespace oihana\models\interfaces;

/**
 * Insert a new document in the model.
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface InsertModel
{
    /**
     * Insert a new item into the model.
     * @param array $init
     * @return mixed
     */
    public function insert( array $init = [] ) :mixed ;
}