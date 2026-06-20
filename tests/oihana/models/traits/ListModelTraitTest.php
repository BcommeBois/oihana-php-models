<?php

namespace tests\oihana\models\traits;

use DI\Container;

use oihana\models\enums\ModelParam;
use oihana\models\interfaces\ListModel;
use oihana\models\traits\ListModelTrait;

use PHPUnit\Framework\TestCase;

use UnexpectedValueException;

final class ListModelTraitTest extends TestCase
{
    private function host(): object
    {
        return new class
        {
            use ListModelTrait;

            public function callAssertListModel(): void { $this->assertListModel() ; }
            public function callInitializeListModel( array $init = [] , $container = null ): static
            {
                return $this->initializeListModel( $init , $container ) ;
            }
        } ;
    }

    public function testAssertListModelThrowsWhenUnset(): void
    {
        $this->expectException( UnexpectedValueException::class ) ;
        $this->host()->callAssertListModel() ;
    }

    public function testAssertListModelPassesWhenSet(): void
    {
        $host = $this->host() ;
        $host->list = $this->createStub( ListModel::class ) ;

        $host->callAssertListModel() ;
        $this->addToAssertionCount( 1 ) ;
    }

    public function testInitializeListModelWithInstance(): void
    {
        $host  = $this->host() ;
        $model = $this->createStub( ListModel::class ) ;

        $host->callInitializeListModel( [ ModelParam::LIST => $model ] ) ;

        $this->assertSame( $model , $host->list ) ;
    }

    public function testInitializeListModelResolvesFromContainer(): void
    {
        $host      = $this->host() ;
        $model     = $this->createStub( ListModel::class ) ;
        $container = new Container() ;
        $container->set( 'list.model' , $model ) ;

        $host->callInitializeListModel( [ ModelParam::LIST => 'list.model' ] , $container ) ;

        $this->assertSame( $model , $host->list ) ;
    }

    public function testInitializeListModelWithUnresolvableYieldsNull(): void
    {
        $host = $this->host() ;
        $host->callInitializeListModel( [ ModelParam::LIST => 'nope' ] , new Container() ) ;
        $this->assertNull( $host->list ) ;
    }
}
