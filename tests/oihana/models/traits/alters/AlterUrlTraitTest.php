<?php

namespace tests\oihana\models\traits\alters ;

use DI\DependencyException;
use DI\NotFoundException;
use oihana\models\enums\Alter;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use stdClass;
use tests\oihana\models\mocks\MockAlterDocument;

class AlterUrlTraitTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testUrlAlteration()
    {
        $processor = new MockAlterDocument
        ([
            'url' => [ Alter::URL , '/users/' ]
        ]);

        $input = [ 'id' => 123, 'url' => 123 ];
        $output = $processor->process($input);

        $this->assertSame('/users/123', $output['url']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testUrlWithCustomPropertyAlteration()
    {
        $processor = new MockAlterDocument
        ([
            'url' => [ Alter::URL, '/profiles/', 'name' ]
        ]);

        $input = [ 'id' => 123, 'name' => 'John', 'url' => '' ];
        $output = $processor->process($input);

        $this->assertSame('/profiles/John', $output['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlSimplePath()
    {
        $processor = new MockAlterDocument
        ([
            'url' => [Alter::URL, '/products']
        ]);

        $input = ['id' => 42, 'url' => ''];
        $output = $processor->process($input);

        $this->assertSame('/products/42', $output['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlWithDefaultAlterKey()
    {
        $processor = new MockAlterDocument
        ([
            'url' => [Alter::URL, '/products']
        ]);

        $processor->alterKey = 'name' ;

        $input = ['id' => 42, 'name' => 'eka' , 'url' => ''];
        $output = $processor->process($input);

        $this->assertSame('/products/eka', $output['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlWithCustomProperty()
    {
        $processor = new MockAlterDocument
        ([
            'url' => [ Alter::URL , '/users' , 'slug' ]
        ]);

        $input = ['id' => 123, 'slug' => 'john-doe', 'url' => '' ] ;
        $output = $processor->process($input);

        $this->assertSame('/users/john-doe', $output['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlWithTrailingSlash()
    {
        $processor = new MockAlterDocument
        ([
            'url' => [Alter::URL, '/api', 'id', null, true]
        ]);

        $input = ['id' => 99, 'url' => ''];
        $output = $processor->process($input);

        $this->assertSame('/api/99/', $output['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlWithEmptyPath()
    {
        $processor = new MockAlterDocument
        ([
            'url' => [Alter::URL, '', 'id']
        ]);

        $input = ['id' => 42, 'url' => ''];
        $output = $processor->process($input);

        $this->assertSame('42', $output['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlWithLeadingSlash()
    {
        $processor = new MockAlterDocument([
            'url' => [Alter::URL, '/api/v1/products', 'id']
        ]);

        $input = ['id' => 123, 'url' => ''];
        $output = $processor->process($input);

        $this->assertSame('/api/v1/products/123', $output['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlWithObject()
    {
        $processor = new MockAlterDocument
        ([
            'url' => [ Alter::URL , '/items' , 'id' ]
        ]);

        $input = new stdClass();
        $input->id = 55;
        $input->url = '';

        $output = $processor->process($input);

        $this->assertSame('/items/55', $output->url);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlWithNumericProperty()
    {
        $processor = new MockAlterDocument
        ([
            'url' => [Alter::URL, '/posts', 'postId']
        ]);

        $input = ['postId' => 1001, 'url' => ''];
        $output = $processor->process($input);

        $this->assertSame('/posts/1001', $output['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlWithStringProperty()
    {
        $processor = new MockAlterDocument([
            'url' => [Alter::URL, '/categories', 'slug']
        ]);

        $input = ['id' => 1, 'slug' => 'electronics', 'url' => ''];
        $output = $processor->process($input);

        $this->assertSame('/categories/electronics', $output['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlWithMissingProperty()
    {
        $processor = new MockAlterDocument([
            'url' => [Alter::URL, '/items', 'code']
        ]);

        $input = ['id' => 42, 'url' => '']; // 'code' is missing
        $output = $processor->process($input);

        // Property value becomes empty string when not found
        $this->assertSame('/items', $output['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlWithTrailingSlashAlreadyPresent()
    {
        $processor = new MockAlterDocument([
            'url' => [Alter::URL, '/api/', 'id', null, true]
        ]);

        $input = ['id' => 88, 'url' => ''];
        $output = $processor->process($input);

        // joinPaths handles duplicate slashes
        $this->assertSame('/api/88/', $output['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlSequentialArray()
    {
        $processor = new MockAlterDocument([
            'url' => [Alter::URL, '/products', 'id']
        ]);

        $input = [
            ['id' => 1, 'url' => ''],
            ['id' => 2, 'url' => ''],
            ['id' => 3, 'url' => ''],
        ];

        $output = $processor->process($input);

        $this->assertSame('/products/1', $output[0]['url']);
        $this->assertSame('/products/2', $output[1]['url']);
        $this->assertSame('/products/3', $output[2]['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlWithNullProperty()
    {
        $processor = new MockAlterDocument([
            'url' => [Alter::URL, '/items', 'id']
        ]);

        $input = ['id' => null, 'url' => ''];
        $output = $processor->process($input);

        // null becomes empty string
        $this->assertSame('/items', $output['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlWithZeroProperty()
    {
        $processor = new MockAlterDocument([
            'url' => [Alter::URL, '/index', 'id']
        ]);

        $input = ['id' => 0, 'url' => ''];
        $output = $processor->process($input);

        $this->assertSame('/index/0', $output['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlWithContainerBaseUrl()
    {
        $processor = new MockAlterDocument([
            'url' => [Alter::URL, '/products', 'id', 'baseUrl']
        ]);

        $processor->container->set('baseUrl', 'https://api.example.com');

        $input = ['id' => 42, 'url' => ''];
        $output = $processor->process($input);

        $this->assertSame('https://api.example.com/products/42', $output['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlWithContainerBaseUrlAndTrailingSlash()
    {
        $processor = new MockAlterDocument([
            'url' => [Alter::URL, '/api', 'id', 'baseUrl', true]
        ]);

        $processor->container->set('baseUrl', 'https://example.com');

        $input = ['id' => 123, 'url' => ''];
        $output = $processor->process($input);

        $this->assertSame('https://example.com/api/123/', $output['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlWithContainerBaseUrlMissing()
    {
        $processor = new MockAlterDocument([
            'url' => [Alter::URL, '/products', 'id', 'baseUrl']
        ]);

        // Container doesn't have 'baseUrl', falls back to path only
        $input = ['id' => 99, 'url' => ''];
        $output = $processor->process($input);

        $this->assertSame('/products/99', $output['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlWithContainerBaseUrlCustomKey()
    {
        $processor = new MockAlterDocument([
            'url' => [Alter::URL, '/items', 'id', 'apiBaseUrl']
        ]);

        $processor->container->set('apiBaseUrl', 'https://api.service.io');

        $input = ['id' => 77, 'url' => ''];
        $output = $processor->process($input);

        $this->assertSame('https://api.service.io/items/77', $output['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlWithContainerBaseUrlNotString()
    {
        $processor = new MockAlterDocument([
            'url' => [Alter::URL, '/products', 'id', 'baseUrl']
        ]);

        // Set non-string value in container (should be ignored)
        $processor->container->set('baseUrl', 12345);

        $input = ['id' => 50, 'url' => ''];
        $output = $processor->process($input);

        // Falls back to path only since baseUrl is not a string
        $this->assertSame('/products/50', $output['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlWithComplexPath()
    {
        $processor = new MockAlterDocument([
            'url' => [Alter::URL, '/v1/api/resources', 'slug', 'baseUrl', true]
        ]);

        $processor->container->set('baseUrl', 'https://cdn.example.com');

        $input = ['slug' => 'my-resource', 'url' => ''];
        $output = $processor->process($input);

        $this->assertSame('https://cdn.example.com/v1/api/resources/my-resource/', $output['url']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function testUrlSequentialArrayWithContainerBaseUrl()
    {
        $processor = new MockAlterDocument([
            'url' => [Alter::URL, '/posts', 'id', 'baseUrl']
        ]);

        $processor->container->set('baseUrl', 'https://blog.example.com');

        $input = [
            ['id' => 1, 'url' => ''],
            ['id' => 2, 'url' => ''],
        ];

        $output = $processor->process($input);

        $this->assertSame('https://blog.example.com/posts/1', $output[0]['url']);
        $this->assertSame('https://blog.example.com/posts/2', $output[1]['url']);
    }
}