<?php

namespace tests\oihana\models\traits\signals;

use oihana\models\traits\signals\HasUpsertSignals;

use PHPUnit\Framework\TestCase;

use oihana\signals\Signal;

class DummyUpsertDocument
{
    use HasUpsertSignals;

    public int $id = 123;
}

class HasUpsertSignalsTest extends TestCase
{
    public function testInitializeUpsertSignalsCreatesSignals()
    {
        $doc = new DummyUpsertDocument();
        $this->assertNull($doc->beforeUpsert);
        $this->assertNull($doc->afterUpsert);

        $doc->initializeUpsertSignals();

        $this->assertInstanceOf(Signal::class, $doc->beforeUpsert);
        $this->assertInstanceOf(Signal::class, $doc->afterUpsert);
    }

    public function testUpsertSignalsEmitDocument()
    {
        $doc = new DummyUpsertDocument();
        $doc->initializeUpsertSignals();

        $captured = null;
        $doc->beforeUpsert?->connect(function($emitted) use (&$captured) { $captured = $emitted; });
        $doc->beforeUpsert?->emit($doc);

        $this->assertSame($doc, $captured);
    }

    public function testReleaseUpsertSignalsNullifiesSignals()
    {
        $doc = new DummyUpsertDocument();
        $doc->initializeUpsertSignals();
        $doc->beforeUpsert?->connect(fn() => null);

        $result = $doc->releaseUpsertSignals();

        $this->assertSame($doc, $result);
        $this->assertNull($doc->beforeUpsert);
        $this->assertNull($doc->afterUpsert);
    }
}
