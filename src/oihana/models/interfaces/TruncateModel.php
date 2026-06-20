<?php

namespace oihana\models\interfaces;

/**
 * Truncate the model documents.
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface TruncateModel
{
    /**
     * Truncate the collection and remove all documents.
     * @param array $init
     * @return mixed
     */
    public function truncate( array $init = [] ) :mixed ;
}