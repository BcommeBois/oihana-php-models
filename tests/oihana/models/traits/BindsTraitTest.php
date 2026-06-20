<?php

namespace tests\oihana\models\traits ;

use oihana\models\traits\BindsTrait;
use PHPUnit\Framework\TestCase;

class MockBinds
{
    use BindsTrait ;
}

class BindsTraitTest extends TestCase
{
    private object $object;

    protected function setUp(): void
    {
        $this->object = new MockBinds() ;
    }

    public function testDefaultBindsIsEmptyArray(): void
    {
        $this->assertIsArray($this->object->binds);
        $this->assertSame([], $this->object->binds);
    }

    public function testInitializeBindsSetsNewBinds(): void
    {
        $init = [
            MockBinds::BINDS => [':id' => 1, ':status' => 'active']
        ];

        $result = $this->object->initializeBinds($init);

        $this->assertSame($this->object, $result);
        $this->assertSame($init[MockBinds::BINDS], $this->object->binds);
    }

    public function testInitializeBindsKeepsExistingWhenInitEmpty(): void
    {
        $this->object->binds = [':foo' => 'bar'];
        $this->object->initializeBinds([]);

        $this->assertSame([':foo' => 'bar'], $this->object->binds);
    }

    public function testPrepareBindVarsMergesDefaultAndRuntimeBinds(): void
    {
        $this->object->binds = [':id' => 42];

        $params = $this->object->prepareBindVars
        ([
            MockBinds::BINDS => [':status' => 'active']
        ]);

        $this->assertSame([
            ':id'     => 42,
            ':status' => 'active'
        ], $params);
    }

    public function testPrepareBindVarsOverridesDefaultValues(): void
    {
        $this->object->binds = [':id' => 42, ':status' => 'pending'];

        $params = $this->object->prepareBindVars([
            MockBinds::BINDS => [':status' => 'active']
        ]);

        $this->assertSame([
            ':id'     => 42,
            ':status' => 'active'
        ], $params);
    }

    public function testPrepareBindVarsWithNoRuntimeBindsReturnsDefault(): void
    {
        $this->object->binds = [':foo' => 'bar'];
        $this->assertSame([':foo' => 'bar'], $this->object->prepareBindVars([]));
    }

    public function testPrepareBindVarsWithNullDefaultBinds(): void
    {
        $this->object->binds = null;

        $params = $this->object->prepareBindVars([
            MockBinds::BINDS => [':a' => 1]
        ]);

        $this->assertSame([':a' => 1], $params);
    }

    public function testPrepareBindVarsWithBothEmpty(): void
    {
        $this->object->binds = [];
        $params = $this->object->prepareBindVars([]);
        $this->assertSame([], $params);
    }

    public function testConstantBindsValue(): void
    {
        $this->assertSame('binds', MockBinds::BINDS);
    }
}