<?php

namespace oihana\models\interfaces;

/**
 * Contract for models able to retrieve a single document.
 *
 * A `GetModel` extends {@see ExistModel}, so an implementation can both test for
 * a document and fetch it. The {@see GetModel::get()} operation returns the
 * resolved document (or value) identified by the supplied options.
 *
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface GetModel extends ExistModel
{
    /**
     * Returns a single document matching the given options.
     *
     * @param array $init Operation options identifying the document to fetch,
     *                    typically a `key`/`id` value, optional `conditions`
     *                    with their `binds` and projection settings.
     *
     * @return mixed The resolved document (commonly an array or object), or a
     *               null/empty value when nothing matches, depending on the
     *               implementation.
     *
     * @example
     * ```php
     * use oihana\models\interfaces\GetModel;
     *
     * class UserModel implements GetModel
     * {
     *     public function exist( array $init = [] ) : bool { return true ; }
     *
     *     public function get( array $init = [] ) : mixed
     *     {
     *         return $this->store->find( $init[ 'key' ] ?? null ) ;
     *     }
     * }
     *
     * $model = new UserModel() ;
     * $user  = $model->get( [ 'key' => 'users/42' ] ) ;
     * ```
     */
    public function get( array $init = [] ) :mixed ;
}
