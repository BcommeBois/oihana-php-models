<?php

namespace oihana\models\interfaces;

use Generator;

/**
 * Interface for models that provide document streaming.
 *
 * This interface defines a method to retrieve documents from a model
 * as a generator, allowing efficient iteration over large datasets
 * without loading all documents into memory at once.
 *
 * @package  oihana\models\interfaces
 * @author   Marc Alcaraz (ekameleon)
 * @since    1.0.0
 */
interface StreamModel
{
    /**
     * Streams documents from the model.
     *
     * This method returns a generator that yields each document one at a time.
     * It is useful for iterating over large collections efficiently, since
     * documents are produced lazily instead of being loaded all at once like
     * {@see ListModel::list()} does.
     *
     * @param array $init Operation options scoping the stream, typically
     *                    `conditions` with their `binds`, a `sort` clause and a
     *                    batch size. An empty array streams every document.
     *
     * @return Generator<mixed> Yields each document in the collection. The type
     *                          of document depends on the model implementation.
     *
     * @example
     * ```php
     * use Generator;
     * use oihana\models\interfaces\StreamModel;
     *
     * class UserModel implements StreamModel
     * {
     *     public function stream( array $init = [] ) : Generator
     *     {
     *         foreach ( $this->store->cursor( $init[ 'conditions' ] ?? null ) as $row )
     *         {
     *             yield $row ;
     *         }
     *     }
     * }
     *
     * $model = new UserModel() ;
     *
     * foreach ( $model->stream( [ 'conditions' => 'doc.active == true' ] ) as $user )
     * {
     *     // process one user at a time, constant memory
     * }
     * ```
     */
    public function stream( array $init = [] ) : Generator ;
}