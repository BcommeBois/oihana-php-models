<?php

namespace oihana\models\traits\signals;

use oihana\signals\Signal;

/**
 * Provides deletion-related signals.
 *
 * This trait defines signals that are emitted **before** and **after** a document
 * is deleted, allowing observers (slots) to react to these events.
 *
 * Signals:
 * - `$beforeDelete`: Emitted before the deletion occurs.
 * - `$afterDelete`: Emitted after the deletion is complete.
 *
 * Example usage:
 * ```php
 * $document->initializeDeleteSignals();
 *
 * $document->beforeDelete?->connect(fn($doc) => echo "About to delete {$doc->id}");
 * $document->afterDelete?->connect(fn($doc) => echo "Deleted document {$doc->id}");
 * ```
 *
 * @author Marc Alcaraz (ekameleon)
 * @since 1.0.0
 * @package oihana\models\traits\signals
 */
trait HasDeleteSignals
{
    /**
     * Signal emitted after a document has been deleted.
     *
     * Observers connected to this signal receive the deleted document and optional context.
     *
     * @var Signal|null
     */
    public ?Signal $afterDelete = null ;

    /**
     * Signal emitted before a document is deleted.
     *
     * Observers connected to this signal receive the document that is about to be deleted.
     *
     * @var Signal|null
     */
    public ?Signal $beforeDelete = null ;

    /**
     * Initializes the deletion-related signals.
     *
     * Creates new Signal instances for `$beforeDelete` and `$afterDelete`.
     *
     * @return static Returns `$this` for method chaining.
     *
     * @example
     * ```php
     * $document->initializeDeleteSignals()
     *          ->beforeDelete?->connect(fn($doc) => echo "About to delete {$doc->id}");
     * ```
     */
    public function initializeDeleteSignals():static
    {
        $this->afterDelete  = new Signal() ;
        $this->beforeDelete = new Signal() ;
        return $this ;
    }

    /**
     * Release the deletion-related signals.
     *
     * Nullify and disconnect the `$beforeDelete` and `$afterDelete` signals.
     *
     * @return static Returns `$this` for method chaining.
     */
    public function releaseDeleteSignals():static
    {
        $this->afterDelete?->disconnect() ;
        $this->beforeDelete?->disconnect() ;

        $this->afterDelete  = null ;
        $this->beforeDelete = null ;

        return $this ;
    }
}