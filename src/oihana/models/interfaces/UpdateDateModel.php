<?php

namespace oihana\models\interfaces;

use org\schema\constants\Schema;

/**
 * Update a single date property in a document with the current date.
 *
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface UpdateDateModel
{
    /**
     * Update a single date property in a document with the current date .
     *
     * By default, it updates the `modified` property with the current timestamp.
     *
     * @param array  $init     Additional options like value, binds, return clause, etc.
     * @param string $property The document property to update (default: Schema::MODIFIED).
     */
    public function updateDate( array $init = [] , string $property = Schema::MODIFIED ) :mixed ;
}