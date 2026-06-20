<?php

namespace oihana\models\enums;

use oihana\reflect\traits\ConstantsTrait;

/**
 * The enumeration of the model's notice types.
 */
class NoticeType
{
    use ConstantsTrait ;

    public const string AFTER_DELETE   = 'afterDelete'   ;
    public const string AFTER_INSERT   = 'afterInsert'   ;
    public const string AFTER_REPLACE  = 'afterReplace'  ;
    public const string AFTER_UPDATE   = 'afterReplace'  ;
    public const string AFTER_TRUNCATE = 'afterTruncate' ;
    public const string AFTER_UPSERT   = 'afterUpsert'   ;

    public const string BEFORE_DELETE   = 'beforeDelete'   ;
    public const string BEFORE_INSERT   = 'beforeInsert'   ;
    public const string BEFORE_REPLACE  = 'beforeReplace'  ;
    public const string BEFORE_TRUNCATE = 'beforeTruncate' ;
    public const string BEFORE_UPDATE   = 'beforeUpdate'   ;
    public const string BEFORE_UPSERT   = 'beforeUpsert'   ;
}