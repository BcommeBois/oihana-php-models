<?php

namespace tests\oihana\models\traits\signals;

use oihana\models\traits\signals\HasReplaceSignals;

use PHPUnit\Framework\TestCase;

use oihana\signals\Signal;

class DummyReplaceDocument
{
    use HasReplaceSignals;

    public int $id = 123;
}

class HasReplaceSignalsTest extends TestCase
{
    public function testInitializeReplaceSignalsCreatesSignals()
    {
        $doc = new DummyReplaceDocument();
        $this->assertNull($doc->beforeReplace);
        $this->assertNull($doc->afterReplace);

        $doc->initializeReplaceSignals();

        $this->assertInstanceOf(Signal::class, $doc->beforeReplace);
        $this->assertInstanceOf(Signal::class, $doc->afterReplace);
    }

    public function testBeforeReplaceSignalEmitsDocument()
    {
        $doc = new DummyReplaceDocument();
        $doc->initializeReplaceSignals();

        $captured = null;
        $doc->beforeReplace?->connect(function($emittedDoc) use (&$captured) {
            $captured = $emittedDoc;
        });

        $doc->beforeReplace?->emit($doc);

        $this->assertSame($doc, $captured);
    }

    public function testAfterReplaceSignalEmitsDocument()
    {
        $doc = new DummyReplaceDocument();
        $doc->initializeReplaceSignals();

        $captured = null;
        $doc->afterReplace?->connect(function($emittedDoc) use (&$captured) {
            $captured = $emittedDoc;
        });

        $doc->afterReplace?->emit($doc);

        $this->assertSame($doc, $captured);
    }

    public function testSignalAutoDisconnect()
    {
        $doc = new DummyReplaceDocument();
        $doc->initializeReplaceSignals();

        $counter = 0;
        $doc->beforeReplace?->connect
        (
            function() use (&$counter) {
                $counter++;
            },
            autoDisconnect: true
        );

        $doc->beforeReplace?->emit();
        $doc->beforeReplace?->emit();

        $this->assertEquals(1, $counter, 'Auto-disconnect should remove the listener after first emit');
    }

    public function testReleaseReplaceSignalsNullifiesSignals()
    {
        $doc = new DummyReplaceDocument();
        $doc->initializeReplaceSignals();
        $doc->beforeReplace?->connect(fn() => null);

        $result = $doc->releaseReplaceSignals();

        $this->assertSame($doc, $result);
        $this->assertNull($doc->beforeReplace);
        $this->assertNull($doc->afterReplace);
    }
}