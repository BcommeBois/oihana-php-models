<?php

namespace oihana\models\interfaces;

/**
 * Contract for models able to delete a document.
 *
 * A `DeleteModel` exposes a single {@see DeleteModel::delete()} operation that
 * removes one (or, depending on the options, several) document(s) from the
 * underlying storage. To wipe an entire collection at once, see
 * {@see DeleteAllModel} or {@see TruncateModel}.
 *
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface DeleteModel
{
    /**
     * Deletes a document, or a set of documents, in the model.
     *
     * @param array $init Operation options identifying what to delete, typically a
     *                    `key`/`id`, a set of `conditions` with their `binds` and
     *                    an optional return clause.
     *
     * @return null|array|object The deleted document(s) when the implementation
     *                           returns them, otherwise `null`.
     *
     * @example
     * ```php
     * use oihana\models\interfaces\DeleteModel;
     *
     * class UserModel implements DeleteModel
     * {
     *     public function delete( array $init = [] ) : null|array|object
     *     {
     *         return $this->store->remove( $init[ 'key' ] ?? null ) ;
     *     }
     * }
     *
     * $model   = new UserModel() ;
     * $removed = $model->delete( [ 'key' => 'users/42' ] ) ;
     * ```
     */
    public function delete( array $init = [] ) :null|array|object ;
}
