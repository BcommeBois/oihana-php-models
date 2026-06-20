<?php

namespace oihana\models\notices;

use oihana\models\enums\NoticeType;
use oihana\signals\notices\Payload;

/**
 * Notice emitted before a document is deleted.
 *
 * Example:
 * ```php
 * $notice = new BeforeDelete
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
class BeforeDelete extends Payload
{
    /**
     * Creates a new BeforeDelete instance.
     * 
     * @param mixed|null  $data    The document(s) or value deleted.
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
            NoticeType::BEFORE_DELETE ,
            $data ,
            $target ,
            $context
        ) ;
    }
}