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

class AlterCallTraitTest extends TestCase
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testCallableAlteration()
    {
        $processor = new MockAlterDocument
        ([
            'score' => [Alter::CALL, fn($v) => $v * 2]
        ]);

        $input = ['score' => 10];
        $output = $processor->process($input);

        $this->assertSame(20, $output['score']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testCallableAlterationWithTwoArguments()
    {
        // On définit une fonction qui prend deux arguments
        $processor = new MockAlterDocument([
            'score' => [Alter::CALL, fn($v, $multiplier) => $v * $multiplier, 3]
        ]);

        $input = ['score' => 10];
        $output = $processor->process($input);

        // La fonction devrait multiplier 10 par 3
        $this->assertSame(30, $output['score']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testCallableAlterationWithStringFunctionCallable()
    {
        $processor = new MockAlterDocument
        ([
            'score' => [Alter::CALL, 'oihana\core\numbers\clip', 2 , 5 ] // le 3e paramètre = facteur
        ]);

        $output = $processor->process(['score' => 7]);
        $this->assertSame(5, $output['score']);

        $output = $processor->process(['score' => 1]);
        $this->assertSame(2 , $output['score']);

        $output = $processor->process(['score' => 3]);
        $this->assertSame(3 , $output['score']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testChainedCallableAlterationWithStringFunctionCallable()
    {
        $processor = new MockAlterDocument
        ([
            'score' => [ Alter::INT , [ Alter::CALL , 'oihana\core\numbers\clip' , 2 , 5 ] ]
        ]);

        $output = $processor->process(['score' => 7.5]);
        $this->assertSame(5, $output['score']);

        $output = $processor->process(['score' => 1.4]);
        $this->assertSame(2 , $output['score']);

        $output = $processor->process(['score' => 3.2]);
        $this->assertSame(3 , $output['score']);
    }
}