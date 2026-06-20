<?php

namespace tests\oihana\models\traits\alters;

use oihana\models\traits\alters\AlterIntPropertyTrait;

use PHPUnit\Framework\TestCase;

final class AlterIntPropertyTraitTest extends TestCase
{
    private object $host;

    protected function setUp(): void
    {
        $this->host = new class { use AlterIntPropertyTrait; } ;
    }

    public function testArrayElementsAreCastToInt(): void
    {
        $modified = false ;
        $this->assertSame( [ 10 , 20 , 30 ] , $this->host->alterIntProperty( [ '10' , '20' , '30' ] , $modified ) ) ;
        $this->assertTrue( $modified ) ;
    }

    public function testScalarIsCastToInt(): void
    {
        $modified = false ;
        $this->assertSame( 42 , $this->host->alterIntProperty( '42' , $modified ) ) ;
        $this->assertTrue( $modified ) ;
    }

    public function testAlreadyIntIsReturnedUnchanged(): void
    {
        $modified = false ;
        $this->assertSame( 5 , $this->host->alterIntProperty( 5 , $modified ) ) ;
        $this->assertFalse( $modified ) ;
    }
}
