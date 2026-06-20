<?php

namespace tests\oihana\models\traits\signals;

use oihana\models\traits\signals\HasInsertSignals;

use PHPUnit\Framework\TestCase;

use oihana\signals\Signal;

class DummyInsertDocument
{
    use HasInsertSignals;

    public int $id = 123;
}

class HasInsertSignalsTest extends TestCase
{
    public function testInitializeInsertSignalsCreatesSignals()
    {
        $doc = new DummyInsertDocument();
        $this->assertNull($doc->beforeInsert);
        $this->assertNull($doc->afterInsert);

        $doc->initializeInsertSignals();

        $this->assertInstanceOf(Signal::class, $doc->beforeInsert);
        $this->assertInstanceOf(Signal::class, $doc->afterInsert);
    }

    public function testBeforeInsertSignalEmitsDocument()
    {
        $doc = new DummyInsertDocument();
        $doc->initializeInsertSignals();

        $captured = null;
        $doc->beforeInsert?->connect(function($emittedDoc) use (&$captured) {
            $captured = $emittedDoc;
        });

        $doc->beforeInsert?->emit($doc);

        $this->assertSame($doc, $captured);
    }

    public function testAfterInsertSignalEmitsDocument()
    {
        $doc = new DummyInsertDocument();
        $doc->initializeInsertSignals();

        $captured = null;
        $doc->afterInsert?->connect(function($emittedDoc) use (&$captured) {
            $captured = $emittedDoc;
        });

        $doc->afterInsert?->emit($doc);

        $this->assertSame($doc, $captured);
    }

    public function testSignalAutoDisconnect()
    {
        $doc = new DummyInsertDocument();
        $doc->initializeInsertSignals();

        $counter = 0;
        $doc->beforeInsert?->connect
        (
            function() use (&$counter) {
                $counter++;
            },
            autoDisconnect: true
        );

        $doc->beforeInsert?->emit();
        $doc->beforeInsert?->emit();

        $this->assertEquals(1, $counter, 'Auto-disconnect should remove the listener after first emit');
    }

    public function testReleaseInsertSignalsNullifiesSignals()
    {
        $doc = new DummyInsertDocument();
        $doc->initializeInsertSignals();
        $doc->beforeInsert?->connect(fn() => null);

        $result = $doc->releaseInsertSignals();

        $this->assertSame($doc, $result);
        $this->assertNull($doc->beforeInsert);
        $this->assertNull($doc->afterInsert);
    }
}