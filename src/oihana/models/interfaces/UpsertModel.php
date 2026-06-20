<?php

namespace oihana\models\interfaces;

/**
 * Contract for models able to upsert a document.
 *
 * An `UpsertModel` exposes a single {@see UpsertModel::upsert()} operation that
 * inserts a document when it does not yet exist, or updates/replaces it when it
 * does. It is the idempotent way to persist a document without first probing for
 * its existence.
 *
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface UpsertModel
{
    /**
     * Inserts or updates a document depending on whether it already exists.
     *
     * @param array $init Operation options carrying the document and its lookup
     *                    keys, typically the matching `conditions`/`key`, the
     *                    `document` payload, optional `binds` and a return clause.
     *
     * @return mixed The upserted document (or its result), depending on the
     *               implementation.
     *
     * @example
     * ```php
     * use oihana\models\interfaces\UpsertModel;
     *
     * class UserModel implements UpsertModel
     * {
     *     public function upsert( array $init = [] ) : mixed
     *     {
     *         return $this->store->upsert( $init[ 'key' ] ?? null, $init[ 'document' ] ?? [] ) ;
     *     }
     * }
     *
     * $model = new UserModel() ;
     * $user  = $model->upsert( [ 'key' => 'users/42', 'document' => [ 'name' => 'Alice' ] ] ) ;
     * ```
     */
    public function upsert( array $init = [] ) :mixed ;
}
