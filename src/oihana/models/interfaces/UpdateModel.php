<?php

namespace oihana\models\interfaces;

/**
 * Contract for models able to update an existing document.
 *
 * An `UpdateModel` exposes a single {@see UpdateModel::update()} operation that
 * applies a partial change to an existing document, merging the supplied fields
 * into the stored one. For a full document overwrite, see {@see ReplaceModel}.
 *
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface UpdateModel
{
    /**
     * Updates an existing document in the model.
     *
     * @param array $init Operation options carrying the change to apply, typically
     *                    the target `key`/`id`, the `document` patch to merge,
     *                    optional `binds` and a return clause.
     *
     * @return mixed The updated document (or its result), depending on the
     *               implementation.
     *
     * @example
     * ```php
     * use oihana\models\interfaces\UpdateModel;
     *
     * class UserModel implements UpdateModel
     * {
     *     public function update( array $init = [] ) : mixed
     *     {
     *         return $this->store->patch( $init[ 'key' ], $init[ 'document' ] ?? [] ) ;
     *     }
     * }
     *
     * $model = new UserModel() ;
     * $user  = $model->update( [ 'key' => 'users/42', 'document' => [ 'active' => false ] ] ) ;
     * ```
     */
    public function update( array $init = [] ) :mixed ;
}
