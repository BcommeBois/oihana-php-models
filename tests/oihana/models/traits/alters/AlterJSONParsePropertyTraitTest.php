<?php

namespace tests\oihana\models\traits\alters;

use oihana\models\traits\alters\AlterJSONParsePropertyTrait;

use PHPUnit\Framework\TestCase;

final class AlterJSONParsePropertyTraitTest extends TestCase
{
    private object $host;

    protected function setUp(): void
    {
        $this->host = new class { use AlterJSONParsePropertyTrait; } ;
    }

    public function testDecodesValidJsonString(): void
    {
        $modified = false ;
        $result = $this->host->alterJsonParseProperty( '{"a":1}' , [ true ] , $modified ) ;

        $this->assertSame( [ 'a' => 1 ] , $result ) ;
        $this->assertTrue( $modified ) ;
    }

    public function testReturnsValueUnchangedWhenNotJson(): void
    {
        $modified = false ;
        $this->assertSame( 'not json' , $this->host->alterJsonParseProperty( 'not json' , [] , $modified ) ) ;
        $this->assertFalse( $modified ) ;
    }
}
