<?php

namespace oihana\models\notices;

use oihana\models\enums\NoticeType;
use oihana\signals\notices\Payload;

/**
 * Notice emitted after a collection has been truncated.
 *
 * Example:
 * ```php
 * $notice = new AfterTruncate
 * (
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
class AfterTruncate extends Payload
{
    /**
     * Creates a new AfterTruncate instance
     *
     * @param object|null $target  The target of the notice.
     * @param array       $context The context of the notice.
     */
    public function __construct
    (
        mixed $target  = null ,
        array $context = []   ,
    )
    {
        parent::__construct
        (
            type    : NoticeType::AFTER_TRUNCATE ,
            target  : $target ,
            context : $context
        ) ;
    }
}