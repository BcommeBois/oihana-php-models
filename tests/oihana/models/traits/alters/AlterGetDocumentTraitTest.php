<?php

namespace tests\oihana\models\traits\alters;

use ReflectionException;

use stdClass;

use PHPUnit\Framework\TestCase;

use DI\DependencyException;
use DI\NotFoundException;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use oihana\models\enums\Alter;

use tests\oihana\models\mocks\MockDocumentsModel;
use tests\oihana\models\mocks\MockAlterDocument;

/**
 * Unit tests for AlterGetDocumentPropertyTrait.
 *
 * This test suite verifies the behavior of the alterGetDocument method,
 * which retrieves documents using a Documents model based on provided values and definitions.
 *
 * @package tests\oihana\models\traits\alters
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class AlterGetDocumentTraitTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentWithDefaultKey()
    {
        $mockModel = new MockDocumentsModel();
        $mockModel->addDocument(['id' => 123, 'name' => 'John Doe', 'email' => 'john@example.com']);

        $processor = new MockAlterDocument([
            'author' => [Alter::GET, 'UserModel']
        ]);

        $processor->container->set('UserModel', $mockModel);

        $input = ['author' => 123];
        $output = $processor->process($input);

        $this->assertIsArray($output['author']);
        $this->assertSame(123, $output['author']['id']);
        $this->assertSame('John Doe', $output['author']['name']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentWithCustomKey()
    {
        $mockModel = new MockDocumentsModel();
        $mockModel->addDocument(['id' => 456, 'slug' => 'jane-doe', 'name' => 'Jane Doe']);

        $processor = new MockAlterDocument([
            'author' => [Alter::GET, 'UserModel', 'slug']
        ]);

        $processor->container->set('UserModel', $mockModel);

        $input = ['author' => 'jane-doe'];
        $output = $processor->process($input);

        $this->assertIsArray($output['author']);
        $this->assertSame('jane-doe', $output['author']['slug']);
        $this->assertSame('Jane Doe', $output['author']['name']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentWithNullValue()
    {
        $mockModel = new MockDocumentsModel();

        $processor = new MockAlterDocument([
            'author' => [Alter::GET, 'UserModel']
        ]);

        $processor->container->set('UserModel', $mockModel);

        $input = ['author' => null];
        $output = $processor->process($input);

        $this->assertNull($output['author']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentNotFound()
    {
        $mockModel = new MockDocumentsModel();
        $mockModel->addDocument(['id' => 123, 'name' => 'John Doe']);

        $processor = new MockAlterDocument([
            'author' => [Alter::GET, 'UserModel']
        ]);

        $processor->container->set('UserModel', $mockModel);

        $input = ['author' => 999]; // Non-existent ID
        $output = $processor->process($input);

        // Model returns null when document not found
        $this->assertNull($output['author']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentWithModelNotInContainer()
    {
        $processor = new MockAlterDocument([
            'author' => [Alter::GET, 'NonExistentModel']
        ]);

        $input = ['author' => 123];
        $output = $processor->process($input);

        // Returns original value when model not found
        $this->assertSame(123, $output['author']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentSequentialArray()
    {
        $mockModel = new MockDocumentsModel();
        $mockModel->addDocument(['id' => 1, 'name' => 'User One']);
        $mockModel->addDocument(['id' => 2, 'name' => 'User Two']);
        $mockModel->addDocument(['id' => 3, 'name' => 'User Three']);

        $processor = new MockAlterDocument([
            'author' => [Alter::GET, 'UserModel']
        ]);

        $processor->container->set('UserModel', $mockModel);

        $input = [
            ['id' => 101, 'author' => 1],
            ['id' => 102, 'author' => 2],
            ['id' => 103, 'author' => 3],
        ];

        $output = $processor->process($input);

        $this->assertSame('User One', $output[0]['author']['name']);
        $this->assertSame('User Two', $output[1]['author']['name']);
        $this->assertSame('User Three', $output[2]['author']['name']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentWithObject()
    {
        $mockModel = new MockDocumentsModel();
        $mockModel->addDocument(['id' => 789, 'name' => 'Object User']);

        $processor = new MockAlterDocument([
            'author' => [Alter::GET, 'UserModel']
        ]);

        $processor->container->set('UserModel', $mockModel);

        $input = new stdClass();
        $input->id = 1;
        $input->author = 789;

        $output = $processor->process($input);

        $this->assertIsArray($output->author);
        $this->assertSame('Object User', $output->author['name']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentWithStringId()
    {
        $mockModel = new MockDocumentsModel();
        $mockModel->addDocument(['id' => 'abc-123', 'name' => 'String ID User']);

        $processor = new MockAlterDocument([
            'author' => [Alter::GET, 'UserModel']
        ]);

        $processor->container->set('UserModel', $mockModel);

        $input = ['author' => 'abc-123'];
        $output = $processor->process($input);

        $this->assertIsArray($output['author']);
        $this->assertSame('String ID User', $output['author']['name']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentWithNumericStringId()
    {
        $mockModel = new MockDocumentsModel();
        $mockModel->addDocument(['id' => '42', 'name' => 'Numeric String User']);

        $processor = new MockAlterDocument([
            'author' => [Alter::GET, 'UserModel']
        ]);

        $processor->container->set('UserModel', $mockModel);

        $input = ['author' => '42'];
        $output = $processor->process($input);

        $this->assertIsArray($output['author']);
        $this->assertSame('Numeric String User', $output['author']['name']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentWithMultipleFields()
    {
        $mockModel = new MockDocumentsModel();
        $mockModel->addDocument([
            'id' => 100,
            'name' => 'Full User',
            'email' => 'full@example.com',
            'role' => 'admin',
            'active' => true
        ]);

        $processor = new MockAlterDocument([
            'author' => [Alter::GET, 'UserModel']
        ]);

        $processor->container->set('UserModel', $mockModel);

        $input = ['author' => 100];
        $output = $processor->process($input);

        $this->assertIsArray($output['author']);
        $this->assertSame('Full User', $output['author']['name']);
        $this->assertSame('full@example.com', $output['author']['email']);
        $this->assertSame('admin', $output['author']['role']);
        $this->assertTrue($output['author']['active']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentChainedWithNormalize()
    {
        $mockModel = new MockDocumentsModel();
        $mockModel->addDocument
        ([
            'id' => 200,
            'name' => 'Chained User',
            'tags' => ['', 'active', null, 'verified']
        ]);

        $processor = new MockAlterDocument
        ([
            'author' =>
            [ [ Alter::GET , 'UserModel' ], Alter::NORMALIZE ]
        ]);

        $processor->container->set('UserModel', $mockModel);

        $input = ['author' => 200];
        $output = $processor->process($input);

        $this->assertIsArray($output['author']);
        $this->assertSame('Chained User', $output['author']['name']);
        // Normalize should clean up the tags array
        $this->assertCount(2, $output['author']['tags']);
        $this->assertContains('active', $output['author']['tags']);
        $this->assertContains('verified', $output['author']['tags']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentWithZeroId()
    {
        $mockModel = new MockDocumentsModel();
        $mockModel->addDocument(['id' => 0, 'name' => 'Zero ID User']);

        $processor = new MockAlterDocument([
            'author' => [Alter::GET, 'UserModel']
        ]);

        $processor->container->set('UserModel', $mockModel);

        $input = ['author' => 0];
        $output = $processor->process($input);

        $this->assertIsArray($output['author']);
        $this->assertSame('Zero ID User', $output['author']['name']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentWithEmptyString()
    {
        $mockModel = new MockDocumentsModel();

        $processor = new MockAlterDocument
        ([
            'author' => [Alter::GET, 'UserModel']
        ]);

        $processor->container->set('UserModel', $mockModel);

        $input = ['author' => '' ];
        $output = $processor->process($input);

        // Empty string should return null
        $this->assertSame(null , $output['author']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentWithMultipleProperties()
    {
        $userModel = new MockDocumentsModel();
        $userModel->addDocument(['id' => 1, 'name' => 'John']);

        $categoryModel = new MockDocumentsModel();
        $categoryModel->addDocument(['id' => 10, 'title' => 'Tech']);

        $processor = new MockAlterDocument([
            'author' => [Alter::GET, 'UserModel'],
            'category' => [Alter::GET, 'CategoryModel']
        ]);

        $processor->container->set('UserModel', $userModel);
        $processor->container->set('CategoryModel', $categoryModel);

        $input = [
            'author' => 1,
            'category' => 10,
            'title' => 'My Article'
        ];

        $output = $processor->process($input);

        $this->assertSame('John', $output['author']['name']);
        $this->assertSame('Tech', $output['category']['title']);
        $this->assertSame('My Article', $output['title']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentWithNestedDocuments()
    {
        $userModel = new MockDocumentsModel();
        $userModel->addDocument([
            'id' => 1,
            'name' => 'John',
            'company' => [
                'id' => 100,
                'name' => 'Acme Corp'
            ]
        ]);

        $processor = new MockAlterDocument([
            'author' => [Alter::GET, 'UserModel']
        ]);

        $processor->container->set('UserModel', $userModel);

        $input = ['author' => 1];
        $output = $processor->process($input);

        $this->assertIsArray($output['author']);
        $this->assertSame('John', $output['author']['name']);
        $this->assertIsArray($output['author']['company']);
        $this->assertSame('Acme Corp', $output['author']['company']['name']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentPreservesOriginalValueWhenModelNull()
    {
        $processor = new MockAlterDocument([
            'author' => [Alter::GET, null]
        ]);

        $input = ['author' => 123];
        $output = $processor->process($input);

        $this->assertSame(123, $output['author']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentWithBooleanValue()
    {
        $mockModel = new MockDocumentsModel();
        $mockModel->addDocument(['id' => true, 'name' => 'Boolean User']);

        $processor = new MockAlterDocument([
            'author' => [Alter::GET, 'UserModel']
        ]);

        $processor->container->set('UserModel', $mockModel);

        $input = ['author' => true];
        $output = $processor->process($input);

        $this->assertIsArray($output['author']);
        $this->assertSame('Boolean User', $output['author']['name']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentWithEmailKey()
    {
        $mockModel = new MockDocumentsModel();
        $mockModel->addDocument([
            'id' => 1,
            'email' => 'user@example.com',
            'name' => 'Email User'
        ]);

        $processor = new MockAlterDocument([
            'author' => [Alter::GET, 'UserModel', 'email']
        ]);

        $processor->container->set('UserModel', $mockModel);

        $input = ['author' => 'user@example.com'];
        $output = $processor->process($input);

        $this->assertIsArray($output['author']);
        $this->assertSame('Email User', $output['author']['name']);
        $this->assertSame('user@example.com', $output['author']['email']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentMixedSequentialArray()
    {
        $mockModel = new MockDocumentsModel();
        $mockModel->addDocument(['id' => 1, 'name' => 'User One']);
        $mockModel->addDocument(['id' => 2, 'name' => 'User Two']);

        $processor = new MockAlterDocument([
            'author' => [Alter::GET, 'UserModel']
        ]);

        $processor->container->set('UserModel', $mockModel);

        $input = [
            ['id' => 101, 'author' => 1, 'title' => 'Post 1'],
            ['id' => 102, 'author' => 999, 'title' => 'Post 2'], // Non-existent author
            ['id' => 103, 'author' => 2, 'title' => 'Post 3'],
        ];

        $output = $processor->process($input);

        $this->assertSame('User One', $output[0]['author']['name']);
        $this->assertNull($output[1]['author']); // Not found
        $this->assertSame('User Two', $output[2]['author']['name']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentWithComplexChaining()
    {
        $mockModel = new MockDocumentsModel();
        $mockModel->addDocument([
            'id' => 1,
            'name' => '  John Doe  ',
            'email' => 'JOHN@EXAMPLE.COM',
            'tags' => ['', 'admin', null, 'verified', '']
        ]);

        $processor = new MockAlterDocument([
            'author' => [
                [Alter::GET, 'UserModel'],
                Alter::NORMALIZE
            ]
        ]);

        $processor->container->set('UserModel', $mockModel);

        $input = ['author' => 1];
        $output = $processor->process($input);

        $this->assertIsArray($output['author']);
        $this->assertSame('  John Doe  ', $output['author']['name']);
        $this->assertCount(2, $output['author']['tags']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentWithUuidKey()
    {
        $mockModel = new MockDocumentsModel();
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $mockModel->addDocument([
            'uuid' => $uuid,
            'name' => 'UUID User'
        ]);

        $processor = new MockAlterDocument([
            'author' => [Alter::GET, 'UserModel', 'uuid']
        ]);

        $processor->container->set('UserModel', $mockModel);

        $input = ['author' => $uuid];
        $output = $processor->process($input);

        $this->assertIsArray($output['author']);
        $this->assertSame('UUID User', $output['author']['name']);
        $this->assertSame($uuid, $output['author']['uuid']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentPreservesOtherProperties()
    {
        $mockModel = new MockDocumentsModel();
        $mockModel->addDocument(['id' => 1, 'name' => 'John']);

        $processor = new MockAlterDocument([
            'author' => [Alter::GET, 'UserModel']
        ]);

        $processor->container->set('UserModel', $mockModel);

        $input = [
            'id' => 999,
            'title' => 'My Article',
            'author' => 1,
            'published' => true,
            'views' => 42
        ];

        $output = $processor->process($input);

        // Check that other properties are preserved
        $this->assertSame(999, $output['id']);
        $this->assertSame('My Article', $output['title']);
        $this->assertTrue($output['published']);
        $this->assertSame(42, $output['views']);
        $this->assertSame('John', $output['author']['name']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetDocumentReturnsNullWhenModelGetThrows(): void
    {
        $throwing = $this->createStub( \oihana\models\interfaces\DocumentsModel::class ) ;
        $throwing->method( 'get' )->willThrowException( new \RuntimeException( 'boom' ) ) ;

        $host = new class { use \oihana\models\traits\alters\AlterGetDocumentPropertyTrait; } ;

        $modified = false ;
        $result   = $host->alterGetDocument( 123 , [ $throwing , 'id' ] , null , $modified ) ;

        $this->assertNull( $result ) ;
        $this->assertFalse( $modified ) ;
    }
}