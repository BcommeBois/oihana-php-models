<?php

namespace tests\oihana\models\traits;

use DI\Container;

use oihana\exceptions\http\Error404;
use oihana\models\interfaces\DocumentsModel;
use oihana\models\interfaces\ExistModel;
use oihana\models\traits\DocumentsTrait;

use PHPUnit\Framework\TestCase;

use tests\oihana\models\mocks\MockDocumentsModel;

final class DocumentsTraitTest extends TestCase
{
    private function host(): object
    {
        $host = new class { use DocumentsTrait; } ;
        $host->container = new Container() ;
        return $host ;
    }

    public function testAssertExistInModelPassesWhenDocumentExists(): void
    {
        $model = $this->createStub( ExistModel::class ) ;
        $model->method( 'exist' )->willReturn( true ) ;

        $this->host()->assertExistInModel( 1 , $model , 'user' ) ;
        $this->addToAssertionCount( 1 ) ; // no exception thrown
    }

    public function testAssertExistInModelThrowsWhenDocumentMissing(): void
    {
        $model = $this->createStub( ExistModel::class ) ;
        $model->method( 'exist' )->willReturn( false ) ;

        $this->expectException( Error404::class ) ;
        $this->host()->assertExistInModel( 999 , $model , 'user' ) ;
    }

    public function testAssertExistInModelWrapsModelException(): void
    {
        $model = $this->createStub( ExistModel::class ) ;
        $model->method( 'exist' )->willThrowException( new \RuntimeException( 'db down' ) ) ;

        $this->expectException( Error404::class ) ;
        $this->host()->assertExistInModel( 1 , $model , 'user' ) ;
    }

    public function testAssertExistInModelReadsKeyFromObject(): void
    {
        $model = $this->createStub( ExistModel::class ) ;
        $model->method( 'exist' )->willReturn( true ) ;

        $document = (object) [ 'id' => 5 ] ;
        $this->host()->assertExistInModel( $document , $model , 'user' ) ;
        $this->addToAssertionCount( 1 ) ;
    }

    public function testGetDocumentsModelResolvesFromContainer(): void
    {
        $host  = $this->host() ;
        $model = new MockDocumentsModel() ;
        $host->container->set( 'model.id' , $model ) ;

        $this->assertSame( $model , $host->getDocumentsModel( 'model.id' ) ) ;
    }

    public function testGetDocumentsModelReturnsInstanceDirectly(): void
    {
        $model = new MockDocumentsModel() ;
        $this->assertSame( $model , $this->host()->getDocumentsModel( $model ) ) ;
    }

    public function testGetDocumentsModelReturnsNullForUnknown(): void
    {
        $this->assertNull( $this->host()->getDocumentsModel( 'unknown' ) ) ;
    }
}
