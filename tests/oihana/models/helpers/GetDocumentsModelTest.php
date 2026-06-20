<?php

namespace tests\oihana\models\helpers;

use DI\Container;

use PHPUnit\Framework\TestCase;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use oihana\models\interfaces\DocumentsModel;

use function oihana\models\helpers\getDocumentsModel;

class GetDocumentsModelTest extends TestCase
{
    private Container      $container ;
    private DocumentsModel $model ;

    protected function setUp(): void
    {
        $this->container = new Container();

        // Création d'un stub pour DocumentsModel
        $this->model = $this->createStub(DocumentsModel::class);

        // On enregistre le stub dans le container
        $this->container->set('my_model', $this->model);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReturnsInstanceIfProvided(): void
    {
        $result = getDocumentsModel($this->model);
        $this->assertSame($this->model, $result);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReturnsInstanceFromContainer(): void
    {
        $result = getDocumentsModel('my_model', $this->container);
        $this->assertSame($this->model, $result);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReturnsDefaultIfStringNotFound(): void
    {
        $default = $this->createStub(DocumentsModel::class);
        $result = getDocumentsModel('unknown_model', $this->container, $default);
        $this->assertSame($default, $result);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReturnsDefaultIfDefinitionIsNull(): void
    {
        $default = $this->createStub(DocumentsModel::class);
        $result = getDocumentsModel(null, $this->container, $default);
        $this->assertSame($default, $result);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReturnsNullIfDefinitionIsNullAndNoDefault(): void
    {
        $result = getDocumentsModel(null, $this->container);
        $this->assertNull($result);
    }

    /**
     * @throws NotFoundExceptionInterface
     */
    public function testThrowsExceptionIfContainerHasError(): void
    {
        $this->expectException(ContainerExceptionInterface::class);

        $mockContainer = $this->createStub(ContainerInterface::class);
        $mockContainer->method('has')->willReturn(true);
        $mockContainer->method('get')->willThrowException(new class extends \Exception implements ContainerExceptionInterface {});

        getDocumentsModel('some_model', $mockContainer);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testThrowsNotFoundExceptionIfContainerDoesNotHave(): void
    {
        $mockContainer = $this->createStub(ContainerInterface::class);
        $mockContainer->method('has')->willReturn(false);

        $default = $this->createStub(DocumentsModel::class);

        // Normalement, ça retourne le default, donc pas d'exception
        $result = getDocumentsModel('some_model', $mockContainer, $default);
        $this->assertSame($default, $result);
    }
}