<?php

namespace tests\oihana\models\notices;

use oihana\models\enums\NoticeType;
use oihana\models\notices\BeforeDelete;
use PHPUnit\Framework\TestCase;
use stdClass;

class BeforeDeleteTest extends TestCase
{
    public function testCanBeInstantiatedWithoutArguments(): void
    {
        $notice = new BeforeDelete();

        $this->assertSame(NoticeType::BEFORE_DELETE, $notice->type);
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

        $notice = new BeforeDelete($data, $target, $context);

        $this->assertSame(NoticeType::BEFORE_DELETE, $notice->type);
        $this->assertSame($data, $notice->data);
        $this->assertSame($target, $notice->target);
        $this->assertSame($context, $notice->context);
    }

    public function testContextDefaultsToEmptyArray(): void
    {
        $notice = new BeforeDelete('some data', new stdClass());

        $this->assertSame([], $notice->context);
    }
}