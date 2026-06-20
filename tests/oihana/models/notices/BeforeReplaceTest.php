<?php

namespace tests\oihana\models\notices;

use oihana\models\enums\NoticeType;
use oihana\models\notices\BeforeReplace;
use PHPUnit\Framework\TestCase;
use stdClass;

class BeforeReplaceTest extends TestCase
{
    public function testCanBeInstantiatedWithoutArguments(): void
    {
        $notice = new BeforeReplace();

        $this->assertSame(NoticeType::BEFORE_REPLACE, $notice->type);
        $this->assertNull($notice->data);
        $this->assertNull($notice->target);
        $this->assertIsArray($notice->context);
        $this->assertEmpty($notice->context);
    }

    public function testCanBeInstantiatedWithArguments(): void
    {
        $data    = ['id' => 123, 'name' => 'Test Document'];
        $target  = new stdClass();
        $context = ['option' => true];

        $notice = new BeforeReplace($data, $target, $context);

        $this->assertSame(NoticeType::BEFORE_REPLACE, $notice->type);
        $this->assertSame($data, $notice->data);
        $this->assertSame($target, $notice->target);
        $this->assertSame($context, $notice->context);
    }

    public function testContextDefaultsToEmptyArray(): void
    {
        $notice = new BeforeReplace('some data', new stdClass());

        $this->assertSame([], $notice->context);
    }
}