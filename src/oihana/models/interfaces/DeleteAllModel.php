<?php

namespace oihana\models\interfaces;

/**
 * Contract for models able to delete a set of documents in one call.
 *
 * A `DeleteAllModel` exposes a single {@see DeleteAllModel::deleteAll()} operation
 * that removes every document matching the supplied options — or the whole
 * collection when no filter is given. Unlike {@see TruncateModel::truncate()},
 * which empties the storage wholesale, `deleteAll()` runs an actual delete query
 * and may therefore honour conditions and return the affected documents.
 *
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface DeleteAllModel
{
    /**
     * Deletes a set of documents in the model.
     *
     * @param array $init Operation options scoping the deletion, typically a set of
     *                    `conditions` with their `binds` and an optional return
     *                    clause. An empty array removes every document.
     *
     * @return mixed The deleted documents or an operation result, depending on the
     *               implementation (commonly an object, an array, or `null`).
     *
     * @example
     * ```php
     * use oihana\models\interfaces\DeleteAllModel;
     *
     * class SessionModel implements DeleteAllModel
     * {
     *     public function deleteAll( array $init = [] ) : mixed
     *     {
     *         return $this->store->removeWhere( $init[ 'conditions' ] ?? null ) ;
     *     }
     * }
     *
     * $model = new SessionModel() ;
     *
     * // remove every expired session
     * $model->deleteAll( [ 'conditions' => 'doc.expiresAt < @now', 'binds' => [ 'now' => time() ] ] ) ;
     * ```
     */
    public function deleteAll( array $init = [] ) :mixed ;
}
