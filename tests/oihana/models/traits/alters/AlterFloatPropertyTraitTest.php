<?php

namespace tests\oihana\models\traits\alters;

use oihana\models\traits\alters\AlterFloatPropertyTrait;

use PHPUnit\Framework\TestCase;

final class AlterFloatPropertyTraitTest extends TestCase
{
    private object $host;

    protected function setUp(): void
    {
        $this->host = new class { use AlterFloatPropertyTrait; } ;
    }

    public function testAlreadyFloatIsReturnedUnchanged(): void
    {
        $modified = false ;
        $this->assertSame( 12.5 , $this->host->alterFloatProperty( 12.5 , $modified ) ) ;
        $this->assertFalse( $modified ) ;
    }

    public function testScalarIsCastToFloat(): void
    {
        $modified = false ;
        $this->assertSame( 12.5 , $this->host->alterFloatProperty( '12.5' , $modified ) ) ;
        $this->assertTrue( $modified ) ;
    }

    public function testArrayElementsAreCastToFloat(): void
    {
        $modified = false ;
        $this->assertSame( [ 1.2 , 3.4 , 5.0 ] , $this->host->alterFloatProperty( [ '1.2' , '3.4' , 5 ] , $modified ) ) ;
        $this->assertTrue( $modified ) ;
    }
}
