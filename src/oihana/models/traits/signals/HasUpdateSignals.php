<?php

namespace oihana\models\traits\signals;

use oihana\signals\Signal;

/**
 * Provides update-related signals.
 *
 * This trait defines signals that are emitted **before** and **after** a document
 * is updated, allowing observers to react to the update event.
 *
 * Signals:
 * - `$beforeUpdate`: Emitted before the update occurs.
 * - `$afterUpdate`: Emitted after the update is complete.
 *
 * @author Marc Alcaraz (ekameleon)
 * @since 1.0.0
 * @package oihana\models\traits\signals
 */
trait HasUpdateSignals
{
    /**
     * Signal emitted after a document has been updated.
     *
     * Observers connected to this signal receive the updated document and optional context.
     *
     * @var Signal|null
     */
    public ?Signal $afterUpdate = null ;

    /**
     * Signal emitted before a document is updated.
     *
     * Observers connected to this signal receive the document that is about to be updated.
     *
     * @var Signal|null
     */
    public ?Signal $beforeUpdate = null ;

    /**
     * Initializes the update-related signals.
     *
     * Creates new Signal instances for `$beforeUpdate` and `$afterUpdate`.
     *
     * @return static Returns `$this` for method chaining.
     *
     * @example
     * ```php
     * $document->initializeUpdateSignals()
     *          ->beforeUpdate?->connect(fn($doc) => echo "About to update {$doc->id}");
     * ```
     */
    public function initializeUpdateSignals():static
    {
        $this->afterUpdate  = new Signal() ;
        $this->beforeUpdate = new Signal() ;
        return $this ;
    }

    /**
     * Release the update-related signals.
     *
     * Nullify and disconnect the `afterUpdate` and `beforeUpdate` signals.
     *
     * @return static Returns `$this` for method chaining.
     */
    public function releaseUpdateSignals():static
    {
        $this->afterUpdate?->disconnect() ;
        $this->beforeUpdate?->disconnect() ;

        $this->afterUpdate  = null ;
        $this->beforeUpdate = null ;

        return $this ;
    }
}