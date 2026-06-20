<?php

namespace tests\oihana\models\mocks;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use oihana\models\traits\AlterDocumentTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

class MockAlterDocument
{
    public function __construct(  array $alters = [])
    {
        $this->alters    = $alters ;
        $this->container = new Container() ;
    }

    use AlterDocumentTrait;

    /**
     * @param mixed $input
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function process( mixed $input ): mixed
    {
        return $this->alter( $input ) ;
    }
}