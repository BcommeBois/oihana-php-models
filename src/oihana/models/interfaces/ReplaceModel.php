<?php

namespace oihana\models\interfaces;

/**
 * Contract for models able to replace an existing document.
 *
 * A `ReplaceModel` exposes a single {@see ReplaceModel::replace()} operation that
 * overwrites a stored document with a brand new payload. Unlike
 * {@see UpdateModel::update()}, which merges a partial patch, `replace()` swaps
 * the whole document — any field absent from the new payload is dropped.
 *
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface ReplaceModel
{
    /**
     * Replaces an existing document in the model.
     *
     * @param array $init Operation options carrying the replacement, typically the
     *                    target `key`/`id`, the full `document` payload, optional
     *                    `binds` and a return clause.
     *
     * @return mixed The replaced document (or its result), depending on the
     *               implementation.
     *
     * @example
     * ```php
     * use oihana\models\interfaces\ReplaceModel;
     *
     * class UserModel implements ReplaceModel
     * {
     *     public function replace( array $init = [] ) : mixed
     *     {
     *         return $this->store->set( $init[ 'key' ], $init[ 'document' ] ?? [] ) ;
     *     }
     * }
     *
     * $model = new UserModel() ;
     * $user  = $model->replace( [ 'key' => 'users/42', 'document' => [ 'name' => 'Bob' ] ] ) ;
     * ```
     */
    public function replace( array $init = [] ) :mixed ;
}
