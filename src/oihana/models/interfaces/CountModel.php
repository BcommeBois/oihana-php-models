<?php

namespace oihana\models\interfaces;

/**
 * Contract for models able to count the documents they manage.
 *
 * A `CountModel` exposes a single {@see CountModel::count()} operation that
 * returns how many documents match the supplied options, without materializing
 * the documents themselves. It is the cheapest way to probe the size of a
 * collection or the cardinality of a filtered query.
 *
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface CountModel
{
    /**
     * Returns the number of documents matching the given options.
     *
     * @param array $init Operation options driving the count, e.g. filtering
     *                    conditions, bind variables (`binds`) or a precompiled
     *                    query. An empty array counts every document.
     *
     * @return int The number of matching documents (`0` when none match).
     *
     * @example
     * ```php
     * use oihana\models\interfaces\CountModel;
     *
     * class UserModel implements CountModel
     * {
     *     public function count( array $init = [] ) : int
     *     {
     *         // run a COUNT query using $init['conditions'] / $init['binds']
     *         return 128 ;
     *     }
     * }
     *
     * $model = new UserModel() ;
     *
     * $total  = $model->count() ;                                  // every user
     * $active = $model->count( [ 'conditions' => 'doc.active == true' ] ) ;
     * ```
     */
    public function count( array $init = [] ) :int ;
}
