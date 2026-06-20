<?php

namespace tests\oihana\models\traits\signals;

use oihana\models\traits\signals\HasTruncateSignals;

use PHPUnit\Framework\TestCase;

use oihana\signals\Signal;

class DummyTruncateDocument
{
    use HasTruncateSignals;

    public int $id = 123;
}

class HasTruncateSignalsTest extends TestCase
{
    public function testInitializeTruncateSignalsCreatesSignals()
    {
        $doc = new DummyTruncateDocument();
        $this->assertNull($doc->beforeTruncate);
        $this->assertNull($doc->afterTruncate);

        $doc->initializeTruncateSignals();

        $this->assertInstanceOf(Signal::class, $doc->beforeTruncate);
        $this->assertInstanceOf(Signal::class, $doc->afterTruncate);
    }

    public function testTruncateSignalsEmitDocument()
    {
        $doc = new DummyTruncateDocument();
        $doc->initializeTruncateSignals();

        $captured = null;
        $doc->beforeTruncate?->connect(function($emitted) use (&$captured) { $captured = $emitted; });
        $doc->beforeTruncate?->emit($doc);

        $this->assertSame($doc, $captured);
    }

    public function testReleaseTruncateSignalsNullifiesSignals()
    {
        $doc = new DummyTruncateDocument();
        $doc->initializeTruncateSignals();
        $doc->beforeTruncate?->connect(fn() => null);

        $result = $doc->releaseTruncateSignals();

        $this->assertSame($doc, $result);
        $this->assertNull($doc->beforeTruncate);
        $this->assertNull($doc->afterTruncate);
    }
}
