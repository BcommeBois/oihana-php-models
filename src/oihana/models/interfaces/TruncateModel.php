<?php

namespace oihana\models\interfaces;

/**
 * Contract for models able to truncate their collection.
 *
 * A `TruncateModel` exposes a single {@see TruncateModel::truncate()} operation
 * that empties the underlying collection, removing every document in one pass.
 * It is the bulk, unconditional counterpart of {@see DeleteAllModel} and is
 * typically far cheaper than deleting documents one by one.
 *
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface TruncateModel
{
    /**
     * Truncates the collection and removes all documents.
     *
     * @param array $init Operation options, e.g. flags forwarded to the storage
     *                    engine. An empty array truncates with default settings.
     *
     * @return mixed The truncation result, depending on the implementation
     *               (commonly a boolean, a count, or `null`).
     *
     * @example
     * ```php
     * use oihana\models\interfaces\TruncateModel;
     *
     * class CacheModel implements TruncateModel
     * {
     *     public function truncate( array $init = [] ) : mixed
     *     {
     *         return $this->store->clear() ;
     *     }
     * }
     *
     * $model = new CacheModel() ;
     * $model->truncate() ; // empties the whole collection
     * ```
     */
    public function truncate( array $init = [] ) :mixed ;
}
