<?php

namespace tests\oihana\models\notices;

use oihana\models\enums\NoticeType;
use oihana\models\notices\BeforeUpsert;
use PHPUnit\Framework\TestCase;
use stdClass;

class BeforeUpsertTest extends TestCase
{
    public function testCanBeInstantiatedWithoutArguments(): void
    {
        $notice = new BeforeUpsert();

        $this->assertSame(NoticeType::BEFORE_UPSERT, $notice->type);
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

        $notice = new BeforeUpsert($data, $target, $context);

        $this->assertSame(NoticeType::BEFORE_UPSERT, $notice->type);
        $this->assertSame($data, $notice->data);
        $this->assertSame($target, $notice->target);
        $this->assertSame($context, $notice->context);
    }

    public function testContextDefaultsToEmptyArray(): void
    {
        $notice = new BeforeUpsert('some data', new stdClass());

        $this->assertSame([], $notice->context);
    }
}