<?php

namespace tests\oihana\models\traits\signals;

use PHPUnit\Framework\TestCase;
use oihana\models\traits\signals\HasDeleteSignals;
use oihana\signals\Signal;

class DummyDeleteDocument
{
    use HasDeleteSignals;

    public int $id = 123;
}

class HasDeleteSignalsTest extends TestCase
{
    public function testInitializeDeleteSignalsCreatesSignals()
    {
        $doc = new DummyDeleteDocument();
        $this->assertNull($doc->beforeDelete);
        $this->assertNull($doc->afterDelete);

        $doc->initializeDeleteSignals();

        $this->assertInstanceOf(Signal::class, $doc->beforeDelete);
        $this->assertInstanceOf(Signal::class, $doc->afterDelete);
    }

    public function testBeforeDeleteSignalEmitsDocument()
    {
        $doc = new DummyDeleteDocument();
        $doc->initializeDeleteSignals();

        $captured = null;
        $doc->beforeDelete?->connect(function($emittedDoc) use (&$captured) {
            $captured = $emittedDoc;
        });

        $doc->beforeDelete?->emit($doc);

        $this->assertSame($doc, $captured);
    }

    public function testAfterDeleteSignalEmitsDocument()
    {
        $doc = new DummyDeleteDocument();
        $doc->initializeDeleteSignals();

        $captured = null;
        $doc->afterDelete?->connect(function($emittedDoc) use (&$captured) {
            $captured = $emittedDoc;
        });

        $doc->afterDelete?->emit($doc);

        $this->assertSame($doc, $captured);
    }

    public function testSignalAutoDisconnect()
    {
        $doc = new DummyDeleteDocument();
        $doc->initializeDeleteSignals();

        $counter = 0;
        $doc->beforeDelete?->connect
        (
            function() use (&$counter) {
                $counter++;
            },
            autoDisconnect: true
        );

        $doc->beforeDelete?->emit();
        $doc->beforeDelete?->emit();

        $this->assertEquals(1, $counter, 'Auto-disconnect should remove the listener after first emit');
    }

    public function testReleaseDeleteSignalsNullifiesSignals()
    {
        $doc = new DummyDeleteDocument();
        $doc->initializeDeleteSignals();
        $doc->beforeDelete?->connect(fn() => null);

        $result = $doc->releaseDeleteSignals();

        $this->assertSame($doc, $result);
        $this->assertNull($doc->beforeDelete);
        $this->assertNull($doc->afterDelete);
    }
}