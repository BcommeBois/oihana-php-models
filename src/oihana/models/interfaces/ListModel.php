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
     * @param array $init Operation options scoping the result set, typically
     *                    `conditions` with their `binds`, a `sort` clause and
     *                    pagination bounds (offset/limit). An empty array lists
     *                    every document.
     *
     * @return array An array of documents or items. The structure and type of
     *               each item depend on the model implementation.
     *
     * @example
     * ```php
     * use oihana\models\interfaces\ListModel;
     *
     * class UserModel implements ListModel
     * {
     *     public function list( array $init = [] ) : array
     *     {
     *         return $this->store->query( $init[ 'conditions' ] ?? null, $init[ 'sort' ] ?? null ) ;
     *     }
     * }
     *
     * $model = new UserModel() ;
     *
     * $all    = $model->list() ;
     * $active = $model->list( [ 'conditions' => 'doc.active == true', 'sort' => 'name' ] ) ;
     * ```
     */
    public function list( array $init = [] ) :array ;
}