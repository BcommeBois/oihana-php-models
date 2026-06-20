<?php

namespace tests\oihana\models\traits;

use DI\Container;

use oihana\models\enums\ModelParam;
use oihana\models\traits\ModelTrait;

use PHPUnit\Framework\TestCase;

use tests\oihana\models\mocks\MockDocumentsModel;

use UnexpectedValueException;

final class ModelTraitTest extends TestCase
{
    private function host(): object
    {
        $host = new class
        {
            use ModelTrait;

            public function callAssertModel(): void { $this->assertModel() ; }
            public function callInitializeModel( array $init = [] ): static { return $this->initializeModel( $init ) ; }
        } ;
        $host->container = new Container() ;
        return $host ;
    }

    public function testAssertModelThrowsWhenUnset(): void
    {
        $host = $this->host() ;
        $host->model = null ;

        $this->expectException( UnexpectedValueException::class ) ;
        $host->callAssertModel() ;
    }

    public function testAssertModelPassesWhenSet(): void
    {
        $host = $this->host() ;
        $host->model = new MockDocumentsModel() ;

        $host->callAssertModel() ;
        $this->addToAssertionCount( 1 ) ;
    }

    public function testInitializeModelWithInstance(): void
    {
        $host  = $this->host() ;
        $model = new MockDocumentsModel() ;

        $host->callInitializeModel( [ ModelParam::MODEL => $model ] ) ;

        $this->assertSame( $model , $host->model ) ;
    }

    public function testInitializeModelResolvesFromContainer(): void
    {
        $host  = $this->host() ;
        $model = new MockDocumentsModel() ;
        $host->container->set( 'model.id' , $model ) ;

        $host->callInitializeModel( [ ModelParam::MODEL => 'model.id' ] ) ;

        $this->assertSame( $model , $host->model ) ;
    }
}
