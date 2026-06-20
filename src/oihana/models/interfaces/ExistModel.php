<?php

namespace oihana\models\interfaces;

/**
 * Contract for models able to test the existence of a document.
 *
 * An `ExistModel` answers a single yes/no question: does a document matching the
 * supplied options exist in the underlying storage? It is typically used as a
 * lightweight guard before a heavier read or write, and is the base interface
 * extended by {@see GetModel} and {@see LastModel}.
 *
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface ExistModel
{
    /**
     * Indicates whether a document matching the given options exists.
     *
     * @param array $init Operation options identifying the document to test,
     *                    typically a `key`/`id` value, a set of `conditions`
     *                    and their `binds`.
     *
     * @return bool `true` if at least one matching document exists, otherwise `false`.
     *
     * @example
     * ```php
     * use oihana\models\interfaces\ExistModel;
     *
     * class UserModel implements ExistModel
     * {
     *     public function exist( array $init = [] ) : bool
     *     {
     *         return isset( $init[ 'key' ] ) && $this->store->has( $init[ 'key' ] ) ;
     *     }
     * }
     *
     * $model = new UserModel() ;
     *
     * if ( $model->exist( [ 'key' => 'users/42' ] ) )
     * {
     *     // safe to fetch or update
     * }
     * ```
     */
    public function exist( array $init = []  ) :bool ;
}
