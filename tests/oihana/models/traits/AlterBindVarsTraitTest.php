<?php

namespace tests\oihana\models\traits;

use DI\Container;

use oihana\models\enums\Alter;
use oihana\models\enums\ModelParam;
use oihana\models\traits\AlterBindVarsTrait;

use PHPUnit\Framework\TestCase;

final class AlterBindVarsTraitTest extends TestCase
{
    private function host( array $bindAlters = [] ): object
    {
        $host = new class { use AlterBindVarsTrait; } ;
        $host->container  = new Container() ;
        $host->bindAlters = $bindAlters ;
        return $host ;
    }

    public function testEmptyBindVarsAreCleaned(): void
    {
        $this->assertSame( [] , $this->host()->alterBindVars( [] ) ) ;
        $this->assertNull( $this->host()->alterBindVars( null ) ) ;
    }

    public function testNonAssociativeBindVarsAreCleanedOnly(): void
    {
        $this->assertSame( [ 'a' , 'b' ] , $this->host()->alterBindVars( [ 'a' , 'b' ] ) ) ;
    }

    public function testAltersAppliedFromContext(): void
    {
        $host = $this->host( [ 'get' => [ 'id' => Alter::INT ] ] ) ;

        $result = $host->alterBindVars( [ 'id' => '42' ] , 'get' ) ;

        $this->assertSame( [ 'id' => 42 ] , $result ) ;
    }

    public function testTopLevelAltersWhenNoContext(): void
    {
        $host = $this->host( [ 'id' => Alter::INT ] ) ;

        $result = $host->alterBindVars( [ 'id' => '7' ] ) ;

        $this->assertSame( [ 'id' => 7 ] , $result ) ;
    }

    public function testEmptyAltersJustCleans(): void
    {
        $host = $this->host( [] ) ;
        $this->assertSame( [ 'id' => '5' ] , $host->alterBindVars( [ 'id' => '5' ] ) ) ;
    }

    public function testInitializeBindVarsAlters(): void
    {
        $host    = $this->host() ;
        $definition = [ 'get' => [ 'id' => Alter::INT ] ] ;

        $host->initializeBindVarsAlters( [ ModelParam::BINDS_ALTERS => $definition ] ) ;

        $this->assertSame( $definition , $host->bindAlters ) ;
    }
}
