<?php

namespace oihana\models\interfaces;

use Generator;

/**
 * Interface for models that provide document streaming.
 *
 * This interface defines a method to retrieve documents from a model
 * as a generator, allowing efficient iteration over large datasets
 * without loading all documents into memory at once.
 *
 * @package  oihana\models\interfaces
 * @author   Marc Alcaraz (ekameleon)
 * @since    1.0.0
 */
interface StreamModel
{
    /**
     * Streams documents from the model.
     *
     * This method returns a generator that yields each document one at a time.
     * It is useful for iterating over large collections efficiently.
     *
     * @param array $init Optional configuration array.
     *
     * @return Generator<mixed>
     * Yields each document in the collection. The type of document depends on the model implementation.
     */
    public function stream( array $init = [] ) : Generator ;
}