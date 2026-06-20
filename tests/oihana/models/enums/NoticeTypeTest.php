<?php

namespace tests\oihana\models\enums;

use oihana\models\enums\NoticeType;
use PHPUnit\Framework\TestCase;

class NoticeTypeTest extends TestCase
{
    /**
     * Freeze the literal value of every constant. The notice tests compare
     * `$notice->type` against these same constants, so a wrong literal stays
     * invisible there (constant === constant); only this test catches it.
     */
    public function testConstantLiteralValues(): void
    {
        $this->assertSame( 'afterDelete'   , NoticeType::AFTER_DELETE   );
        $this->assertSame( 'afterInsert'   , NoticeType::AFTER_INSERT   );
        $this->assertSame( 'afterReplace'  , NoticeType::AFTER_REPLACE  );
        $this->assertSame( 'afterUpdate'   , NoticeType::AFTER_UPDATE   );
        $this->assertSame( 'afterTruncate' , NoticeType::AFTER_TRUNCATE );
        $this->assertSame( 'afterUpsert'   , NoticeType::AFTER_UPSERT   );

        $this->assertSame( 'beforeDelete'   , NoticeType::BEFORE_DELETE   );
        $this->assertSame( 'beforeInsert'   , NoticeType::BEFORE_INSERT   );
        $this->assertSame( 'beforeReplace'  , NoticeType::BEFORE_REPLACE  );
        $this->assertSame( 'beforeTruncate' , NoticeType::BEFORE_TRUNCATE );
        $this->assertSame( 'beforeUpdate'   , NoticeType::BEFORE_UPDATE   );
        $this->assertSame( 'beforeUpsert'   , NoticeType::BEFORE_UPSERT   );
    }

    /**
     * Every notice type must be unique. This guards against copy-paste
     * collisions such as AFTER_UPDATE accidentally sharing AFTER_REPLACE's
     * value, which would make the two notices indistinguishable by type.
     */
    public function testAllValuesAreUnique(): void
    {
        $values = NoticeType::getAll();

        $this->assertCount( count( $values ) , array_unique( array_values( $values ) ) );
    }

    /**
     * The before/after pair for the `update` verb must not collide with the
     * `replace` verb — the specific regression this test was written for.
     */
    public function testUpdateIsDistinctFromReplace(): void
    {
        $this->assertNotSame( NoticeType::AFTER_REPLACE  , NoticeType::AFTER_UPDATE  );
        $this->assertNotSame( NoticeType::BEFORE_REPLACE , NoticeType::BEFORE_UPDATE );
    }
}
