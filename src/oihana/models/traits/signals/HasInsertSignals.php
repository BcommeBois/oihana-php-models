<?php

namespace oihana\models\traits\signals;

use oihana\signals\Signal;

/**
 * Provides insertion-related signals.
 *
 * This trait defines signals that are emitted **before** and **after** a document
 * is inserted, allowing observers (slots) to react to these events.
 *
 * Signals:
 * - `$beforeInsert`: Emitted before the insertion occurs.
 * - `$afterInsert`: Emitted after the insertion is complete.
 *
 * Example usage:
 * ```php
 * $document->initializeInsertSignals();
 *
 * $document->beforeInsert?->connect(fn($doc) => echo "About to insert {$doc->id}");
 * $document->afterInsert?->connect(fn($doc) => echo "Inserted document {$doc->id}");
 * ```
 *
 * @author Marc Alcaraz (ekameleon)
 * @since 1.0.0
 * @package oihana\models\traits\signals
 */
trait HasInsertSignals
{
    /**
     * Signal emitted after a document has been inserted.
     *
     * Observers connected to this signal receive the inserted document as a parameter.
     *
     * @var Signal|null
     */
    public ?Signal $afterInsert = null ;

    /**
     * Signal emitted before a document is inserted.
     *
     * Observers connected to this signal receive the document that is about to be inserted.
     *
     * @var Signal|null
     */
    public ?Signal $beforeInsert = null ;

    /**
     * Initializes the insertion-related signals.
     *
     * Creates new Signal instances for `$beforeInsert` and `$afterInsert`.
     *
     * @return static Returns `$this` for method chaining.
     *
     * @example
     * ```php
     * $document->initializeInsertSignals()
     *          ->beforeInsert?->connect(fn($doc) => echo "About to insert {$doc->id}");
     * ```
     */
    public function initializeInsertSignals():static
    {
        $this->afterInsert  = new Signal() ;
        $this->beforeInsert = new Signal() ;
        return $this ;
    }

    /**
     * Release the insertion-related signals.
     *
     * Nullify and disconnect the `afterInsert` and `beforeInsert` signals.
     *
     * @return static Returns `$this` for method chaining.
     */
    public function releaseInsertSignals():static
    {
        $this->afterInsert?->disconnect() ;
        $this->beforeInsert?->disconnect() ;

        $this->afterInsert  = null ;
        $this->beforeInsert = null ;

        return $this ;
    }
}