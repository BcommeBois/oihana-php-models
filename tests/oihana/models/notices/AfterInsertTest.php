<?php

namespace tests\oihana\models\notices;

use oihana\models\enums\NoticeType;
use oihana\models\notices\AfterInsert;
use PHPUnit\Framework\TestCase;
use stdClass;

class AfterInsertTest extends TestCase
{
    public function testCanBeInstantiatedWithoutArguments(): void
    {
        $notice = new AfterInsert();

        $this->assertSame(NoticeType::AFTER_INSERT, $notice->type);
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

        $notice = new AfterInsert($data, $target, $context);

        $this->assertSame(NoticeType::AFTER_INSERT, $notice->type);
        $this->assertSame($data, $notice->data);
        $this->assertSame($target, $notice->target);
        $this->assertSame($context, $notice->context);
    }

    public function testContextDefaultsToEmptyArray(): void
    {
        $notice = new AfterInsert('some data', new stdClass());

        $this->assertSame([], $notice->context);
    }
}