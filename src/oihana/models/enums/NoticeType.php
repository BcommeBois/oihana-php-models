<?php

namespace oihana\models\enums;

use oihana\reflect\traits\ConstantsTrait;

/**
 * Enumeration of the model's notice types.
 *
 * Each constant is the **type identifier** carried by a notice
 * {@see \oihana\models\notices} payload when a model emits a lifecycle event.
 * The values are paired around every CRUD verb (`delete`, `insert`, `replace`,
 * `truncate`, `update`, `upsert`) with a `before*` and an `after*` variant:
 *
 * - `before*` types are emitted **before** the operation runs, so observers can
 *   inspect or veto the document that is about to change.
 * - `after*` types are emitted **once the operation has completed**, carrying
 *   the resulting document(s).
 *
 * Always reference these constants instead of the raw string so that a routing
 * or filtering layer keeps working if a value ever changes (no *magic strings*).
 *
 * Example:
 * ```php
 * use oihana\models\enums\NoticeType;
 * use oihana\models\notices\AfterUpdate;
 *
 * $notice = new AfterUpdate( data: $result, target: $this, context: $init );
 *
 * if ( $notice->type === NoticeType::AFTER_UPDATE )
 * {
 *     // react to a completed update
 * }
 * ```
 *
 * @package oihana\models\enums
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 *
 * @see \oihana\models\notices For the notice payload classes that use these types.
 * @see \oihana\models\traits\signals For the signals that emit the notices.
 */
class NoticeType
{
    use ConstantsTrait ;

    /**
     * Emitted after a document has been deleted.
     */
    public const string AFTER_DELETE = 'afterDelete' ;

    /**
     * Emitted after a document has been inserted.
     */
    public const string AFTER_INSERT = 'afterInsert' ;

    /**
     * Emitted after a document has been replaced.
     */
    public const string AFTER_REPLACE = 'afterReplace' ;

    /**
     * Emitted after a collection has been truncated.
     */
    public const string AFTER_TRUNCATE = 'afterTruncate' ;

    /**
     * Emitted after a document has been updated.
     */
    public const string AFTER_UPDATE = 'afterUpdate' ;

    /**
     * Emitted after a document has been upserted (inserted or updated).
     */
    public const string AFTER_UPSERT = 'afterUpsert' ;

    /**
     * Emitted before a document is deleted.
     */
    public const string BEFORE_DELETE = 'beforeDelete' ;

    /**
     * Emitted before a document is inserted.
     */
    public const string BEFORE_INSERT = 'beforeInsert' ;

    /**
     * Emitted before a document is replaced.
     */
    public const string BEFORE_REPLACE = 'beforeReplace' ;

    /**
     * Emitted before a collection is truncated.
     */
    public const string BEFORE_TRUNCATE = 'beforeTruncate' ;

    /**
     * Emitted before a document is updated.
     */
    public const string BEFORE_UPDATE = 'beforeUpdate' ;

    /**
     * Emitted before a document is upserted (inserted or updated).
     */
    public const string BEFORE_UPSERT = 'beforeUpsert' ;
}
