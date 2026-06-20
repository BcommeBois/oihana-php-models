<?php

namespace oihana\models\traits\signals;

use oihana\signals\Signal;

/**
 * Provides upsert-related signals.
 *
 * This trait defines signals that are emitted **before** and **after** a document
 * is upserted, allowing observers to react to the update event.
 *
 * Signals:
 * - `$beforeUpsert`: Emitted before the update occurs.
 * - `$afterUpsert`: Emitted after the update is complete.
 *
 * @author Marc Alcaraz (ekameleon)
 * @since 1.0.0
 * @package oihana\models\traits\signals
 */
trait HasUpsertSignals
{
    /**
     * Signal emitted after a document has been upserted.
     *
     * Observers connected to this signal receive the upserted document and optional context.
     *
     * @var Signal|null
     */
    public ?Signal $afterUpsert = null ;

    /**
     * Signal emitted before a document is upserted.
     *
     * Observers connected to this signal receive the document that is about to be updated.
     *
     * @var Signal|null
     */
    public ?Signal $beforeUpsert = null ;

    /**
     * Initializes the upsert-related signals.
     *
     * Creates new Signal instances for `$beforeUpsert` and `$afterUpsert`.
     *
     * @return static Returns `$this` for method chaining.
     *
     * @example
     * ```php
     * $document->initializeUpsertSignals()
     *          ->beforeUpsert?->connect(fn($doc) => echo "About to upsert {$doc->id}");
     * ```
     */
    public function initializeUpsertSignals():static
    {
        $this->afterUpsert  = new Signal() ;
        $this->beforeUpsert = new Signal() ;
        return $this ;
    }

    /**
     * Release the upsert-related signals.
     *
     * Nullify and disconnect the `afterUpsert` and `beforeUpsert` signals.
     *
     * @return static Returns `$this` for method chaining.
     */
    public function releaseUpsertSignals():static
    {
        $this->afterUpsert?->disconnect() ;
        $this->beforeUpsert?->disconnect() ;

        $this->afterUpsert  = null ;
        $this->beforeUpsert = null ;

        return $this ;
    }
}