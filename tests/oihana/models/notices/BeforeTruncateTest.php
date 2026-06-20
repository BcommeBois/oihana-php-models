<?php

namespace tests\oihana\models\notices;

use oihana\models\enums\NoticeType;
use oihana\models\notices\BeforeTruncate;
use PHPUnit\Framework\TestCase;
use stdClass;

class BeforeTruncateTest extends TestCase
{
    public function testCanBeInstantiatedWithoutArguments(): void
    {
        $notice = new BeforeTruncate();

        $this->assertSame(NoticeType::BEFORE_TRUNCATE, $notice->type);
        $this->assertNull($notice->data);
        $this->assertNull($notice->target);
        $this->assertIsArray($notice->context);
        $this->assertEmpty($notice->context);
    }

    public function testCanBeInstantiatedWithArguments(): void
    {
        $target  = new stdClass();
        $context = ['option' => true];

        $notice = new BeforeTruncate($target, $context);

        $this->assertSame(NoticeType::BEFORE_TRUNCATE, $notice->type);
        $this->assertNull( $notice->data);
        $this->assertSame($target, $notice->target);
        $this->assertSame($context, $notice->context);
    }

    public function testContextDefaultsToEmptyArray(): void
    {
        $notice = new BeforeTruncate(new stdClass());

        $this->assertSame([], $notice->context);
    }
}