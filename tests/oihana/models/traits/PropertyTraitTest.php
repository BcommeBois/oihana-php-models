<?php

namespace tests\oihana\models\traits ;

use oihana\models\traits\PropertyTrait;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class PropertyTraitTest extends TestCase
{
    use PropertyTrait;

    /**
     * Create a mock class using the PropertyTrait for testing.
     */
    private function getMock(): object
    {
        return new class {
            use PropertyTrait;
        };
    }

    public function testInitializePropertyWithArray()
    {
        $this->assertNull($this->property, 'Property should initially be null');

        $this->initializeProperty(['property' => 'foo']);

        $this->assertSame('foo', $this->property, 'Property should be set from init array');
    }

    public function testInitializePropertyReturnsSelf()
    {
        $return = $this->initializeProperty(['property' => 'baz']);

        $this->assertSame($this, $return, 'initializeProperty should return $this for chaining');
    }

    public function testInitializePropertyWithNullValue()
    {
        $this->property = 'initial';

        $this->initializeProperty(['property' => null]);

        $this->assertNull($this->property, 'Property should be set to null if init array has null');
    }

    public function testAssertPropertyThrowsExceptionWhenNull(): void
    {
        $mock = $this->getMock();
        $mock->initializeProperty([]); // property is null

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The "property" key is not set.');

        $mock->assertProperty();
    }

    public function testChainingInitializeAndAssert(): void
    {
        $mock = $this->getMock();

        // Chain initializeProperty and assertProperty
        $result = $mock->initializeProperty(['property' => 'foo'])->assertProperty();
        $this->assertSame($mock, $result);
        $this->assertSame('foo', $mock->property);
    }
}