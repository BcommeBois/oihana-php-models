<?php

namespace tests\oihana\models\traits\alters ;

use DI\DependencyException;
use DI\NotFoundException;

use oihana\core\arrays\CleanFlag;
use oihana\models\enums\Alter;

use PHPUnit\Framework\TestCase;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use ReflectionException;
use tests\oihana\models\mocks\MockAlterDocument;

class AlterNormalizeTraitTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testNormalizeWithDefaultFlags()
    {
        $processor = new MockAlterDocument([
            'name' => Alter::NORMALIZE
        ]);

        $input = ['name' => '  John  '];
        $output = $processor->process($input);

        $this->assertSame('John', $output['name']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testAlterWithScalarNull()
    {
        $processor = new MockAlterDocument([
            'value' => Alter::NORMALIZE
        ]);

        $input = null;
        $output = $processor->process($input);

        // Scalar null returned unchanged
        $this->assertNull($output);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testNormalizeEmptyString()
    {
        $processor = new MockAlterDocument([
            'description' => Alter::NORMALIZE
        ]);

        $input = ['description' => '   '];
        $output = $processor->process($input);

        $this->assertNull($output['description']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testNormalizeNullValue()
    {
        $processor = new MockAlterDocument([
            'value' => Alter::NORMALIZE
        ]);

        $input = ['value' => null];
        $output = $processor->process($input);

        $this->assertNull($output['value']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testNormalizeWithCustomFlags()
    {
        $processor = new MockAlterDocument([
            'status' => [Alter::NORMALIZE, CleanFlag::NULLS]
        ]);

        $input = ['status' => '   '];
        $output = $processor->process($input);

        // With only NULLS flag, whitespace-only strings are NOT treated as empty
        $this->assertSame('   ', $output['status']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testNormalizeArray()
    {
        $processor = new MockAlterDocument([
            'tags' => Alter::NORMALIZE
        ]);

        $input = ['tags' => ['a', '', null, '  b  ']];
        $output = $processor->process($input);

        // Empty strings and nulls removed, ...
        $this->assertSame(['a', '  b  '], $output['tags']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testNormalizeNestedArray()
    {
        $processor = new MockAlterDocument([
            'data' => Alter::NORMALIZE
        ]);

        $input = ['data' => [
            'items' => ['a', '', null, 'b'],
            'count' => 2
        ]];
        $output = $processor->process($input);

        // Recursive cleaning removes empty/null from nested arrays
        $this->assertSame(['items' => ['a', 'b'], 'count' => 2], $output['data']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testNormalizeEmptyArray()
    {
        $processor = new MockAlterDocument([
            'options' => Alter::NORMALIZE
        ]);

        $input = ['options' => ['', null, '  ']];
        $output = $processor->process($input);

        // Empty array returns null with RETURN_NULL flag
        $this->assertNull($output['options']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testNormalizeWithFalsyFlag()
    {
        $processor = new MockAlterDocument([
            'count' => [Alter::NORMALIZE, CleanFlag::FALSY]
        ]);

        $input = ['count' => 0];
        $output = $processor->process($input);

        $this->assertNull($output['count']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testNormalizeWithoutReturnNullFlag()
    {
        $processor = new MockAlterDocument
        ([
            'tags' => [Alter::NORMALIZE, CleanFlag::DEFAULT ]
        ]);

        $input = ['tags' => ['', null, '  ']];
        $output = $processor->process($input);

        // Returns empty array instead of null
        $this->assertSame([], $output['tags']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testNormalizePreservesValidValues()
    {
        $processor = new MockAlterDocument([
            'email' => Alter::NORMALIZE
        ]);

        $input = ['email' => 'john@example.com'];
        $output = $processor->process($input);

        $this->assertSame('john@example.com', $output['email']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testNormalizeIntegerNotFalsy()
    {
        $processor = new MockAlterDocument([
            'score' => [Alter::NORMALIZE, CleanFlag::NULLS | CleanFlag::EMPTY]
        ]);

        $input = ['score' => 0];
        $output = $processor->process($input);

        // 0 is preserved without FALSY flag
        $this->assertSame(0, $output['score']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testNormalizeSequentialArray()
    {
        $processor = new MockAlterDocument([
            'name' => Alter::NORMALIZE
        ]);

        $input = [
            ['name' => '  Alice  '],
            ['name' => '   '],
            ['name' => 'Bob'],
        ];

        $output = $processor->process($input);

        $this->assertSame('Alice', $output[0]['name']);
        $this->assertNull($output[1]['name']);
        $this->assertSame('Bob', $output[2]['name']);
    }
}