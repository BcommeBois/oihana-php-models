<?php

namespace tests\oihana\models\traits ;

use Closure;
use InvalidArgumentException;
use oihana\models\enums\ModelParam;
use oihana\models\traits\SchemaTrait;
use PHPUnit\Framework\TestCase;

class MockSchema
{
    use SchemaTrait ;
}

class SchemaTraitTest extends TestCase
{
    private object $object;

    protected function setUp(): void
    {
        $this->object = new MockSchema() ;
    }

    public function testDefaultSchemaIsNull(): void
    {
        $this->assertNull($this->object->schema, 'Default schema should be null.');
    }

    public function testInitializeSchemaSetsValue(): void
    {
        $expected = 'org\\schema\\Thing';

        $result = $this->object->initializeSchema([ ModelParam::SCHEMA => $expected ]);

        $this->assertSame($this->object, $result, 'Method should return the same instance (fluent interface).');
        $this->assertSame($expected, $this->object->schema, 'Schema property should be set correctly.');
    }

    public function testInitializeSchemaWithoutKeyKeepsNull(): void
    {
        $result = $this->object->initializeSchema([]);

        $this->assertSame($this->object, $result);
        $this->assertNull($this->object->schema, 'Schema should remain null when key is missing.');
    }

    public function testInitializeSchemaOverridesExistingValue(): void
    {
        // Set an initial value
        $this->object->schema = 'org\\schema\\Person';

        $newValue = 'org\\schema\\Organization';
        $this->object->initializeSchema([ ModelParam::SCHEMA => $newValue ]);

        $this->assertSame($newValue, $this->object->schema, 'Existing schema should be overridden.');
    }

    public function testInitializeSchemaWithClosure(): void
    {
        $closure = fn() => 'org\\schema\\Event';

        $result = $this->object->initializeSchema([ModelParam::SCHEMA => $closure]);

        $this->assertSame($this->object, $result, 'Method should return the same instance.');
        $this->assertInstanceOf(Closure::class, $this->object->schema, 'Schema should be a Closure.');
    }

    public function testInitializeSchemaWithAnonymousFunction(): void
    {
        $function = function() {
            return 'org\\schema\\Product';
        };

        $this->object->initializeSchema([ModelParam::SCHEMA => $function]);

        $this->assertInstanceOf(Closure::class, $this->object->schema, 'Schema should be a Closure.');
    }

    public function testInitializeSchemaThrowsExceptionForInvalidType(): void
    {
        $this->expectException( InvalidArgumentException::class );
        $this->expectExceptionMessage('The `schema` property must be a string or Closure');

        $this->object->initializeSchema([ModelParam::SCHEMA => 123]);
    }

    public function testInitializeSchemaThrowsExceptionForArray(): void
    {
        $this->expectException( InvalidArgumentException::class );

        $this->object->initializeSchema([ModelParam::SCHEMA => ['SomeClass', 'method']]);
    }

    public function testHasSchemaReturnsFalseByDefault(): void
    {
        $this->assertFalse($this->object->hasSchema(), 'hasSchema should return false when schema is null.');
    }

    public function testHasSchemaReturnsTrueWithString(): void
    {
        $this->object->initializeSchema([ModelParam::SCHEMA => 'org\\schema\\Place']);

        $this->assertTrue($this->object->hasSchema(), 'hasSchema should return true when schema is set.');
    }

    public function testHasSchemaReturnsTrueWithClosure(): void
    {
        $this->object->initializeSchema([ModelParam::SCHEMA => fn() => 'test']);

        $this->assertTrue($this->object->hasSchema(), 'hasSchema should return true when schema is a Closure.');
    }

    public function testGetSchemaReturnsStringDirectly(): void
    {
        $expected = 'org\\schema\\Article';
        $this->object->initializeSchema([ModelParam::SCHEMA => $expected]);

        $result = $this->object->getSchema();

        $this->assertSame($expected, $result, 'getSchema should return the string value directly.');
    }

    public function testGetSchemaExecutesClosure(): void
    {
        $expected = 'org\\schema\\Movie';
        $closure = fn() => $expected;

        $this->object->initializeSchema([ModelParam::SCHEMA => $closure]);

        $result = $this->object->getSchema();

        $this->assertSame($expected, $result, 'getSchema should execute the Closure and return its result.');
    }

    public function testGetSchemaExecutesAnonymousFunction(): void
    {
        $prefix = 'org\\schema\\';
        $function = function() use ($prefix) {
            return $prefix . 'Book';
        };

        $this->object->initializeSchema([ModelParam::SCHEMA => $function]);

        $result = $this->object->getSchema();

        $this->assertSame('org\\schema\\Book', $result, 'getSchema should execute the anonymous function.');
    }

    public function testGetSchemaReturnsNullWhenSchemaIsNull(): void
    {
        $result = $this->object->getSchema();

        $this->assertNull($result, 'getSchema should return null when schema is not set.');
    }

    public function testGetSchemaExecutesClosureMultipleTimes(): void
    {
        $counter = 0;
        $closure = function() use (&$counter) {
            $counter++;
            return 'org\\schema\\Item_' . $counter;
        };

        $this->object->initializeSchema([ModelParam::SCHEMA => $closure]);

        $result1 = $this->object->getSchema();
        $result2 = $this->object->getSchema();

        $this->assertSame('org\\schema\\Item_1', $result1, 'First call should return Item_1.');
        $this->assertSame('org\\schema\\Item_2', $result2, 'Second call should return Item_2.');
        $this->assertSame(2, $counter, 'Closure should be executed twice.');
    }

    public function testGetSchemaResolvesViaSchemaResolver(): void
    {
        $this->object->schema = new \org\schema\helpers\SchemaResolver(
            'type',
            [ 'article' => 'org\\schema\\Thing' ],
        );

        $this->assertSame( 'org\\schema\\Thing' , $this->object->getSchema([ 'type' => 'article' ]) );
    }
}