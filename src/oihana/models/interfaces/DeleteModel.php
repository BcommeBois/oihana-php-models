<?php

namespace oihana\models\interfaces;

/**
 * Delete a document in the model.
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface DeleteModel
{
    /**
     * Deletes an document or a set of documents in the model.
     * @param array $init
     * @return null|array|object
     */
    public function delete( array $init = [] ) :null|array|object ;
}