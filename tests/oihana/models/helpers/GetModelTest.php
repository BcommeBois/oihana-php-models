<?php

namespace tests\oihana\models\helpers;

use DI\Container;

use oihana\models\enums\ModelParam;
use oihana\models\Model;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function oihana\models\helpers\getModel;

class GetModelTest extends TestCase
{
    private Container $container ;
    private Model     $model ;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function setUp(): void
    {
        $this->container = new Container() ;
        $this->model     = new Model( $this->container ) ;

        $this->container->set( 'model' , $this->model ) ;
        $this->container->set( 'string' , 'hello world' ) ;
    }

    /**
     * Test: Direct Model instance is returned as-is
     */
    public function testReturnsDirectModelInstance(): void
    {
        $result = getModel( $this->model );
        $this->assertSame($this->model, $result);
    }

    /**
     * Test: Null definition with no default returns null
     */
    public function testReturnsNullWhenDefinitionIsNull(): void
    {
        $result = getModel();
        $this->assertNull($result);
    }

    /**
     * Test: Default model is returned when definition is null
     */
    public function testReturnsDefaultWhenDefinitionIsNull(): void
    {
        $result = getModel( default: $this->model);
        $this->assertSame( $this->model , $result);
    }

    /**
     * Test: Array with MODEL key extracts and returns the model
     */
    public function testExtractsModelFromArray(): void
    {
        $definition = [ ModelParam::MODEL => $this->model ];
        $result = getModel($definition);
        $this->assertSame($this->model, $result);
    }

    /**
     * Test: Array without MODEL key returns default
     */
    public function testArrayWithoutModelKeyReturnsDefault(): void
    {
        $definition = ['other_key' => 'value'];
        $result = getModel($definition, null, $this->model ) ;
        $this->assertSame( $this->model , $result );
    }

    /**
     * Test: String identifier resolved from container
     */
    public function testResolvesStringFromContainer(): void
    {
        $result = getModel('model', $this->container ) ;
        $this->assertSame($this->model, $result);
    }

    /**
     * Test: String not found in container returns default
     */
    public function testReturnsDefaultWhenStringNotInContainer(): void
    {
        $result = getModel('unknown', $this->container , $this->model) ;
        $this->assertSame($this->model, $result);
    }

    /**
     * Test: String with no container returns default
     */
    public function testReturnsDefaultWhenContainerIsNull(): void
    {
        $result = getModel('unknown', null , $this->model) ;
        $this->assertSame($this->model, $result);
    }

    /**
     * Test: Container returns non-Model instance, returns default
     */
    public function testReturnsDefaultWhenContainerReturnsNonModel(): void
    {
        $result = getModel('string', null , $this->model) ;
        $this->assertSame($this->model, $result);
    }

    /**
     * Test: Array extracted value is resolved from container
     */
    public function testResolvesArrayExtractedValueFromContainer(): void
    {
        $definition = [ModelParam::MODEL => 'model'];
        $result = getModel($definition, $this->container);
        $this->assertSame($this->model, $result);
    }

    /**
     * Test: Priority - direct instance takes precedence over all
     */
    public function testDirectInstanceTakesPrecedence(): void
    {
        $definition = [ModelParam::MODEL => 'ignored'];
        $result = getModel( $definition, $this->container , $this->model );
        $this->assertSame($this->model, $result);
    }
}