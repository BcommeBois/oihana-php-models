<?php

namespace oihana\models\notices;

use oihana\models\enums\NoticeType;
use oihana\signals\notices\Payload;

/**
 * Notice emitted after a document has been updated.
 *
 * Example:
 * ```php
 * $notice = new AfterUpdate
 * (
 *     data    : $result,
 *     target  : $this,
 *     context : $init
 * );
 * $signal->emit( $notice ) ;
 * ```
 *
 * @author Marc Alcaraz (ekameleon)
 * @since 1.0.0
 * @package oihana\notices
 */
class AfterUpdate extends Payload
{
    /**
     * Creates a new AfterUpdate instance
     *
     * @param mixed|null  $data    The document(s) or value updated.
     * @param object|null $target  The target of the notice.
     * @param array       $context The context of the notice.
     */
    public function __construct
    (
        mixed $data    = null ,
        mixed $target  = null ,
        array $context = []   ,
    )
    {
        parent::__construct
        (
            NoticeType::AFTER_UPDATE ,
            $data ,
            $target ,
            $context
        ) ;
    }
}