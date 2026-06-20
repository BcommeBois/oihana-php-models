<?php

namespace oihana\models\interfaces;

/**
 * Interface for models that provide a list of documents or items.
 *
 * This interface defines a method to retrieve all documents from a model
 * as an array. It is suitable for cases where the dataset is small enough
 * to be loaded entirely into memory.
 *
 * @package  oihana\models\interfaces
 * @author   Marc Alcaraz (ekameleon)
 * @since    1.0.0
 */
interface ListModel
{
    /**
     * Returns a collection of items from the model.
     *
     * Retrieves all documents as an array. For large datasets, consider using
     * a streaming approach (e.g., {@see StreamModel}) to avoid high memory usage.
     *
     * @param array $init Optional configuration array.
     *
     * @return array
     * An array of documents or items. The structure and type of each item depend on the model implementation.
     */
    public function list( array $init = [] ) :array ;
}