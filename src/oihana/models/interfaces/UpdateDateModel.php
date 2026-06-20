<?php

namespace oihana\models\interfaces;

use org\schema\constants\Schema;

/**
 * Contract for models able to touch a single date property of a document.
 *
 * An `UpdateDateModel` exposes a {@see UpdateDateModel::updateDate()} operation
 * that stamps one date field of a document with the current date — by default
 * the `modified` property ({@see Schema::MODIFIED}). It is the lightweight way to
 * record a "last touched" timestamp without rewriting the rest of the document.
 *
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface UpdateDateModel
{
    /**
     * Updates a single date property in a document with the current date.
     *
     * By default, it updates the `modified` property with the current timestamp.
     *
     * @param array  $init     Operation options identifying the target document,
     *                         typically a `key`/`id`, an explicit `value` to use
     *                         instead of "now", optional `binds` and a return clause.
     * @param string $property The document property to update (default: {@see Schema::MODIFIED}).
     *
     * @return mixed The updated document (or operation result), depending on the
     *               implementation.
     *
     * @example
     * ```php
     * use oihana\models\interfaces\UpdateDateModel;
     * use org\schema\constants\Schema;
     *
     * class UserModel implements UpdateDateModel
     * {
     *     public function updateDate( array $init = [] , string $property = Schema::MODIFIED ) : mixed
     *     {
     *         return $this->store->touch( $init[ 'key' ] ?? null, $property, date( 'c' ) ) ;
     *     }
     * }
     *
     * $model = new UserModel() ;
     *
     * $model->updateDate( [ 'key' => 'users/42' ] ) ;                       // stamps `modified`
     * $model->updateDate( [ 'key' => 'users/42' ], Schema::DATE_PUBLISHED ) ; // stamps another field
     * ```
     */
    public function updateDate( array $init = [] , string $property = Schema::MODIFIED ) :mixed ;
}