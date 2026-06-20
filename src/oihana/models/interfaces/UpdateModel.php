<?php

namespace oihana\models\interfaces;

/**
 * Update a document in the model.
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface UpdateModel
{
    /**
     * Update an item into the model.
     * @param array $init
     * @return mixed
     */
    public function update( array $init = [] ) :mixed ;
}