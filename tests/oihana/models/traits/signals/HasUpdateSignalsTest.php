<?php

namespace tests\oihana\models\traits\signals;

use oihana\models\traits\signals\HasUpdateSignals;

use PHPUnit\Framework\TestCase;

use oihana\signals\Signal;

class DummyUpdateDocument
{
    use HasUpdateSignals;

    public int $id = 123;
}

class HasUpdateSignalsTest extends TestCase
{
    public function testInitializeUpdateSignalsCreatesSignals()
    {
        $doc = new DummyUpdateDocument();
        $this->assertNull($doc->beforeUpdate);
        $this->assertNull($doc->afterUpdate);

        $doc->initializeUpdateSignals();

        $this->assertInstanceOf(Signal::class, $doc->beforeUpdate);
        $this->assertInstanceOf(Signal::class, $doc->afterUpdate);
    }

    public function testBeforeUpdateSignalEmitsDocument()
    {
        $doc = new DummyUpdateDocument();
        $doc->initializeUpdateSignals();

        $captured = null;
        $doc->beforeUpdate?->connect(function($emittedDoc) use (&$captured) {
            $captured = $emittedDoc;
        });

        $doc->beforeUpdate?->emit($doc);

        $this->assertSame($doc, $captured);
    }

    public function testAfterUpdateSignalEmitsDocument()
    {
        $doc = new DummyUpdateDocument();
        $doc->initializeUpdateSignals();

        $captured = null;
        $doc->afterUpdate?->connect(function($emittedDoc) use (&$captured) {
            $captured = $emittedDoc;
        });

        $doc->afterUpdate?->emit($doc);

        $this->assertSame($doc, $captured);
    }

    public function testSignalAutoDisconnect()
    {
        $doc = new DummyUpdateDocument();
        $doc->initializeUpdateSignals();

        $counter = 0;
        $doc->beforeUpdate?->connect
        (
            function() use (&$counter) {
                $counter++;
            },
            autoDisconnect: true
        );

        $doc->beforeUpdate?->emit();
        $doc->beforeUpdate?->emit();

        $this->assertEquals(1, $counter, 'Auto-disconnect should remove the listener after first emit');
    }

    public function testReleaseUpdateSignalsNullifiesSignals()
    {
        $doc = new DummyUpdateDocument();
        $doc->initializeUpdateSignals();
        $doc->beforeUpdate?->connect(fn() => null);

        $result = $doc->releaseUpdateSignals();

        $this->assertSame($doc, $result);
        $this->assertNull($doc->beforeUpdate);
        $this->assertNull($doc->afterUpdate);
    }
}