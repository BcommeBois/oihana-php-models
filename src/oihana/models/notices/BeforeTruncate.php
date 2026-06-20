<?php

namespace oihana\models\notices;

use oihana\models\enums\NoticeType;
use oihana\signals\notices\Payload;

/**
 * Notice emitted before a collection is truncated.
 *
 * Example:
 * ```php
 * $notice = new BeforeTruncate
 * (
 *     target  : $this,
 *     context : $init
 * );
 *
 * $signal->emit( $notice ) ;
 * ```
 *
 * @author Marc Alcaraz (ekameleon)
 * @since 1.0.0
 * @package oihana\notices
 */
class BeforeTruncate extends Payload
{
    /**
     * Creates a new BeforeTruncate instance.
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
            type    : NoticeType::BEFORE_TRUNCATE ,
            target  : $target ,
            context : $context
        ) ;
    }
}