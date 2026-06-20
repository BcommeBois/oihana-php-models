<?php

namespace tests\oihana\models\traits\alters;

use oihana\models\traits\alters\AlterValueTrait;

use PHPUnit\Framework\TestCase;

final class AlterValueTraitTest extends TestCase
{
    private object $host;

    protected function setUp(): void
    {
        $this->host = new class { use AlterValueTrait; } ;
    }

    public function testReplacesWhenDifferent(): void
    {
        $modified = false ;
        $this->assertSame( 'published' , $this->host->alterValue( 'draft' , [ 'published' ] , $modified ) ) ;
        $this->assertTrue( $modified ) ;
    }

    public function testKeepsValueWhenIdentical(): void
    {
        $modified = false ;
        $this->assertSame( 'published' , $this->host->alterValue( 'published' , [ 'published' ] , $modified ) ) ;
        $this->assertFalse( $modified ) ;
    }
}
