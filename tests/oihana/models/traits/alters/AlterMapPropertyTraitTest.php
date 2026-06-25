<?php

namespace tests\oihana\models\traits\alters;

use oihana\models\traits\alters\AlterMapPropertyTrait;

use PHPUnit\Framework\TestCase;

class MapCallbacks
{
    public static function upper( array|object $document , $container , string $key , mixed $value , array $params = [] ): string
    {
        return strtoupper( (string) $value ) ;
    }
}

final class AlterMapPropertyTraitTest extends TestCase
{
    private object $host;

    protected function setUp(): void
    {
        $this->host = new class { use AlterMapPropertyTrait; } ;
    }

    public function testAppliesTheCallable(): void
    {
        $document = [ 'price' => 10 , 'vat' => 0.2 ] ;
        $callback = fn( $doc , $container , $key , $value , $params ) => $value + ( $value * $doc['vat'] ) ;

        $modified = false ;
        $result   = $this->host->alterMapProperty( $document , null , 'price' , 10 , [ $callback ] , $modified ) ;

        $this->assertSame( 12.0 , $result ) ;
        $this->assertTrue( $modified ) ;
    }

    public function testResolvesAStringCallable(): void
    {
        $document = [] ;
        $modified = false ;

        $result = $this->host->alterMapProperty( $document , null , 'value' , 'abc' , [ MapCallbacks::class . '::upper' ] , $modified ) ;

        $this->assertSame( 'ABC' , $result ) ;
        $this->assertTrue( $modified ) ;
    }

    public function testReturnsValueWhenNoParams(): void
    {
        $document = [] ;
        $modified = false ;

        $this->assertSame( 'unchanged' , $this->host->alterMapProperty( $document , null , 'k' , 'unchanged' , [] , $modified ) ) ;
        $this->assertFalse( $modified ) ;
    }

    public function testForwardsTheContextToTheCallbackAsLastArgument(): void
    {
        $document = [] ;
        $modified = false ;
        $context  = [ 'skin' => 'full' , 'locale' => 'fr' ] ;

        $seen     = null ;
        $callback = function( $doc , $container , $key , $value , $params , $ctx = [] ) use ( &$seen )
        {
            $seen = $ctx ;
            return $value ;
        } ;

        $this->host->alterMapProperty( $document , null , 'k' , 'v' , [ $callback ] , $modified , $context ) ;

        $this->assertSame( $context , $seen ) ;
        $this->assertTrue( $modified ) ;
    }

    public function testContextDefaultsToEmptyArrayWhenOmitted(): void
    {
        $document = [] ;
        $modified = false ;

        $seen     = null ;
        $callback = function( $doc , $container , $key , $value , $params , $ctx = null ) use ( &$seen )
        {
            $seen = $ctx ;
            return $value ;
        } ;

        $this->host->alterMapProperty( $document , null , 'k' , 'v' , [ $callback ] , $modified ) ;

        $this->assertSame( [] , $seen ) ;
    }

    public function testLegacyFiveArgCallbackKeepsWorking(): void
    {
        $document = [] ;
        $modified = false ;

        // A callback that does NOT declare the trailing $context still works (PHP discards the surplus arg).
        $callback = fn( $doc , $container , $key , $value , $params ) => strtoupper( (string) $value ) ;

        $result = $this->host->alterMapProperty( $document , null , 'k' , 'abc' , [ $callback ] , $modified , [ 'skin' => 'full' ] ) ;

        $this->assertSame( 'ABC' , $result ) ;
        $this->assertTrue( $modified ) ;
    }
}
