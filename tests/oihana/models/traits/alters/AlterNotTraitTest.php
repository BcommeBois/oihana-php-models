<?php

namespace tests\oihana\models\traits\alters ;

use DI\DependencyException;
use DI\NotFoundException;
use oihana\models\enums\Alter;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use tests\oihana\models\mocks\MockAlterDocument;

class AlterNotTraitTest extends TestCase
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testAlterWithScalarBoolean()
    {
        $processor = new MockAlterDocument
        ([
            'active' => Alter::NOT
        ]);

        $output = $processor->process(true);

        // Scalar boolean returned unchanged
        $this->assertSame(true, $output);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testNotAlterationSingleBoolean()
    {
        $processor = new MockAlterDocument
        ([
            'active' => Alter::NOT
        ]);

        $input = ['active' => true];
        $output = $processor->process($input);

        $this->assertSame(false, $output['active']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testNotAlterationArrayOfBooleans()
    {
        $processor = new MockAlterDocument([
            'flags' => Alter::NOT
        ]);

        $input = ['flags' => [true, false, true]];
        $output = $processor->process($input);

        $this->assertSame([false, true, false], $output['flags']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testNotAlterationNonBooleanValue()
    {
        $processor = new MockAlterDocument([
            'enabled' => Alter::NOT
        ]);

        $input = ['enabled' => 1]; // truthy value
        $output = $processor->process($input);

        $this->assertSame(false, $output['enabled']);
    }
}