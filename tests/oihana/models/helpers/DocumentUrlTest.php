<?php

namespace tests\oihana\models\helpers;

use DI\Container;
use PHPUnit\Framework\TestCase;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function oihana\models\helpers\documentUrl;

class DocumentUrlTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReturnsPathWhenNoContainer()
    {
        $path = 'uploads/file.txt';
        $url = documentUrl($path);
        $this->assertSame($path, $url);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAppendsTrailingSlash()
    {
        $path = 'uploads';
        $url = documentUrl($path, null, 'baseUrl', true);
        $this->assertSame('uploads/', $url);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUsesContainerBaseUrl()
    {
        $container = new Container() ;

        $container->set( 'baseUrl' , 'https://example.com' ) ;

        $path = 'uploads/file.txt';



        $url = documentUrl( path: $path, container: $container ) ;

        $this->assertSame('https://example.com/uploads/file.txt', $url);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testTrailingSlashWithContainer()
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn('https://example.com');

        $url = documentUrl('uploads', $container, 'baseUrl', true);
        $this->assertSame('https://example.com/uploads/', $url);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCustomDefinitionKey()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->with('customUrl')->willReturn(true);
        $container->method('get')->with('customUrl')->willReturn('https://cdn.example.com');

        $url = documentUrl('images/photo.jpg', $container, 'customUrl');
        $this->assertSame('https://cdn.example.com/images/photo.jpg', $url);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testMissingDefinitionFallsBackToPath()
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(false);

        $url = documentUrl('uploads/file.txt', $container);
        $this->assertSame('uploads/file.txt', $url);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testEmptyPathAndContainer()
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn('https://example.com');

        $url = documentUrl('', $container);
        $this->assertSame('https://example.com', $url);
    }
}