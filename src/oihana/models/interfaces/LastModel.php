<?php

namespace oihana\models\interfaces;

/**
 * Contract for models able to fetch their most recent document.
 *
 * A `LastModel` extends {@see ExistModel} and adds a {@see LastModel::last()}
 * operation returning the "latest" document. By default an implementation sorts
 * on the `modified` property, so `last()` yields the most recently changed
 * document, but the ordering can be overridden through the options.
 *
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface LastModel extends ExistModel
{
    /**
     * Returns the last document in the model.
     *
     * @param array $init Operation options driving the lookup, typically the
     *                    `sort` field used to determine "last" (default: the
     *                    `modified` property), optional `conditions` and `binds`.
     *
     * @return mixed The last matching document, or a null/empty value when the
     *               collection is empty, depending on the implementation.
     *
     * @example
     * ```php
     * use oihana\models\interfaces\LastModel;
     *
     * class LogModel implements LastModel
     * {
     *     public function exist( array $init = [] ) : bool { return true ; }
     *
     *     public function last( array $init = [] ) : mixed
     *     {
     *         return $this->store->latest( $init[ 'sort' ] ?? 'modified' ) ;
     *     }
     * }
     *
     * $model      = new LogModel() ;
     * $lastEntry  = $model->last() ;                       // by 'modified'
     * $lastByDate = $model->last( [ 'sort' => 'created' ] ) ;
     * ```
     */
    public function last( array $init = [] ) :mixed ;
}
