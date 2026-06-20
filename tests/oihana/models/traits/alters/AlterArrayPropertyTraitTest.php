<?php

namespace tests\oihana\models\traits\alters;

use DI\Container;

use oihana\models\enums\Alter;

use PHPUnit\Framework\TestCase;

use tests\oihana\models\mocks\MockAlterDocument;
use tests\oihana\models\mocks\MockDocumentsModel;

final class AlterArrayPropertyTraitTest extends TestCase
{
    private function host(): MockAlterDocument
    {
        return new MockAlterDocument() ;
    }

    public function testStringIsExplodedThenIntCast(): void
    {
        $modified = false ;
        $result   = $this->host()->alterArrayProperty( '1;2;3' , [ Alter::INT ] , null , $modified ) ;

        $this->assertSame( [ 1 , 2 , 3 ] , $result ) ;
        $this->assertTrue( $modified ) ;
    }

    public function testNonStringNonArrayBecomesEmptyArray(): void
    {
        $modified = false ;
        $this->assertSame( [] , $this->host()->alterArrayProperty( 42 , [] , null , $modified ) ) ;
    }

    public function testCleanArm(): void
    {
        $result = $this->host()->alterArrayElements( [ 'a' , '' , 'b' ] , [ Alter::CLEAN ] ) ;
        $this->assertSame( [ 'a' , 'b' ] , array_values( $result ) ) ;
    }

    public function testFloatAndIntArms(): void
    {
        $this->assertSame( [ 1.5 , 2.0 ] , $this->host()->alterArrayElements( [ '1.5' , 2 ] , [ Alter::FLOAT ] ) ) ;
        $this->assertSame( [ 1 , 2 ]     , $this->host()->alterArrayElements( [ '1' , '2' ] , [ Alter::INT ] ) ) ;
    }

    public function testNotArm(): void
    {
        $this->assertSame( [ false , true ] , $this->host()->alterArrayElements( [ true , false ] , [ Alter::NOT ] ) ) ;
    }

    public function testJsonParseArm(): void
    {
        $result = $this->host()->alterArrayElements( [ '{"a":1}' ] , [ Alter::JSON_PARSE ] ) ;
        $this->assertEquals( (object) [ 'a' => 1 ] , $result[0] ) ;
    }

    public function testCallArm(): void
    {
        $result = $this->host()->alterArrayElements( [ 'a' , 'b' ] , [ [ Alter::CALL , 'strtoupper' ] ] ) ;
        $this->assertSame( [ 'A' , 'B' ] , $result ) ;
    }

    public function testNormalizeArm(): void
    {
        $result = $this->host()->alterArrayElements( [ [ 'x' , '' , null , 'y' ] ] , [ Alter::NORMALIZE ] ) ;
        $this->assertIsArray( $result ) ;
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetArm(): void
    {
        $model = new MockDocumentsModel() ;
        $model->addDocument( [ 'id' => 1 , 'name' => 'One' ] ) ;

        $host = $this->host() ;
        $host->container = new Container() ;
        $host->container->set( 'UserModel' , $model ) ;

        $result = $host->alterArrayElements( [ 1 ] , [ [ Alter::GET , 'UserModel' ] ] , $host->container ) ;
        $this->assertSame( 'One' , $result[0]['name'] ) ;
    }

    public function testDefaultArmLeavesArrayUnchanged(): void
    {
        $this->assertSame( [ 'a' , 'b' ] , $this->host()->alterArrayElements( [ 'a' , 'b' ] , [ Alter::VALUE ] ) ) ;
    }

    public function testNoOptionsLeavesArrayUnchanged(): void
    {
        $this->assertSame( [ 'a' ] , $this->host()->alterArrayElements( [ 'a' ] , [] ) ) ;
    }
}
