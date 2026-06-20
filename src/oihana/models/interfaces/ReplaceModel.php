<?php

namespace oihana\models\interfaces;

/**
 * Replace a document in the model.
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface ReplaceModel
{
    /**
     * Replace a document into the model.
     * @param array $init
     * @return mixed
     */
    public function replace( array $init = [] ) :mixed ;
}