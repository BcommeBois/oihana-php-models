<?php

namespace tests\oihana\models\traits\alters;

use oihana\models\traits\alters\AlterHydratePropertyTrait;

use org\schema\Thing;

use PHPUnit\Framework\TestCase;

class PlainTarget
{
    public ?string $name = null ;
    public ?int    $age  = null ;
}

final class AlterHydratePropertyTraitTest extends TestCase
{
    private object $host;

    protected function setUp(): void
    {
        $this->host = new class { use AlterHydratePropertyTrait; } ;
    }

    public function testNonArrayValueIsReturnedUnchanged(): void
    {
        $modified = false ;
        $this->assertSame( 'scalar' , $this->host->alterHydrateProperty( 'scalar' , [ Thing::class ] , $modified ) ) ;
        $this->assertFalse( $modified ) ;
    }

    public function testEmptyArrayReturnsNull(): void
    {
        $modified = false ;
        $this->assertNull( $this->host->alterHydrateProperty( [] , [ Thing::class ] , $modified ) ) ;
        $this->assertTrue( $modified ) ;
    }

    public function testHydratesIntoAThingSubclass(): void
    {
        $modified = false ;
        $result   = $this->host->alterHydrateProperty( [ 'name' => 'Alice' ] , [ Thing::class ] , $modified ) ;

        $this->assertInstanceOf( Thing::class , $result ) ;
        $this->assertSame( 'Alice' , $result->name ) ;
        $this->assertTrue( $modified ) ;
    }

    public function testHydratesIntoAPlainClassViaReflection(): void
    {
        $modified = false ;
        $result   = $this->host->alterHydrateProperty( [ 'name' => 'Bob' , 'age' => 30 ] , [ PlainTarget::class ] , $modified ) ;

        $this->assertInstanceOf( PlainTarget::class , $result ) ;
        $this->assertSame( 'Bob' , $result->name ) ;
        $this->assertSame( 30 , $result->age ) ;
        $this->assertTrue( $modified ) ;
    }

    public function testWithoutSchemaReturnsTheNormalizedArray(): void
    {
        $modified = false ;
        $result   = $this->host->alterHydrateProperty( [ 'name' => 'Carol' ] , [] , $modified ) ;

        $this->assertSame( [ 'name' => 'Carol' ] , $result ) ;
    }
}
