<?php

namespace oihana\models\interfaces;

/**
 * Contract for models able to insert a new document.
 *
 * An `InsertModel` exposes a single {@see InsertModel::insert()} operation that
 * creates a new document in the underlying storage. Implementations usually emit
 * `beforeInsert`/`afterInsert` signals around the operation so observers can
 * inspect or react to the created document.
 *
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface InsertModel
{
    /**
     * Inserts a new document into the model.
     *
     * @param array $init Operation options carrying the data to insert, typically
     *                    the `document` payload, optional `binds` and a return
     *                    clause describing what the call should yield.
     *
     * @return mixed The inserted document (or its identifier/result), depending
     *               on the implementation.
     *
     * @example
     * ```php
     * use oihana\models\interfaces\InsertModel;
     *
     * class UserModel implements InsertModel
     * {
     *     public function insert( array $init = [] ) : mixed
     *     {
     *         return $this->store->add( $init[ 'document' ] ?? [] ) ;
     *     }
     * }
     *
     * $model = new UserModel() ;
     * $user  = $model->insert( [ 'document' => [ 'name' => 'Alice' ] ] ) ;
     * ```
     */
    public function insert( array $init = [] ) :mixed ;
}
