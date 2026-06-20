<?php

namespace tests\oihana\models\pdo ;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use oihana\models\enums\ModelParam;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use tests\oihana\traits\mocks\MockPDOClass;

class PDOTraitTest extends TestCase
{
    /**
     * @var MockPDOClass
     */
    private MockPDOClass $model;

    protected function setUp(): void
    {
        $this->model = new MockPDOClass();
    }

    /**
     * @throws Exception
     */
    public function testBindValuesWithSimpleBindings(): void
    {
        $mock = $this->createMock(PDOStatement::class);

        $expectedCalls =
        [
            [':id', 123],
            [':name', 'foo']
        ];

        $callIndex = 0;

        $mock
        ->method('bindValue')
        ->with( $this->callback(function ($arg1) use (&$callIndex, $expectedCalls)
        {
            return is_string($arg1); // verify the first value only
        }),

        $this->callback(function ($arg2) use (&$callIndex, $expectedCalls)
        {
            return true ; // We won't filter on the 2nd value here, we'll do it lower overall.
        }),
        $this->anything());

        // On surcharge la méthode bindValue pour vérifier les appels un à un
        $mock->method('bindValue')
              ->willReturnCallback(function ($key, $value, $type = null) use (&$callIndex, $expectedCalls)
              {
                $expected = $expectedCalls[$callIndex];
                $this->assertSame($expected[0], $key);
                $this->assertSame($expected[1], $value);
                $callIndex++;
                return true;
            });

        $this->model->bindValues($mock, ['id' => 123, 'name' => 'foo']);
    }

    /**
     * @throws Exception
     */
    public function testBindValuesWithTypedBindings(): void
    {
        $stmt = $this->createMock(PDOStatement::class);

        $stmt->method('bindValue')
             ->with(':count', 42, PDO::PARAM_INT);

        $this->model->bindValues($stmt, ['count' => [42, PDO::PARAM_INT]]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function testFetchReturnsNullIfNoStatement(): void
    {
        $this->model->pdo = null;
        $result = $this->model->fetch('SELECT * FROM table');
        $this->assertNull($result);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function testFetchReturnsNullIfExecuteFails(): void
    {
        $stmt = $this->createStub(PDOStatement::class);
        $stmt->method('execute')->willReturn(false);

        $pdo = $this->createStub(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->model->pdo = $pdo;
        $result = $this->model->fetch('SELECT * FROM table');
        $this->assertNull($result);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function testFetchReturnsResultObject(): void
    {
        $row = ['id' => 1, 'name' => 'test'];

        $stmt = $this->createStub(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn($row);
        $stmt->method('closeCursor');
        $stmt->method('setFetchMode');

        $pdo = $this->createStub(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->model->pdo = $pdo;

        $result = $this->model->fetch('SELECT * FROM table');

        $this->assertIsObject($result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('test', $result->name);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testFetchAllReturnsEmptyArrayOnNoStatement(): void
    {
        $this->model->pdo = null;
        $result = $this->model->fetchAll('SELECT * FROM table');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function testFetchAllReturnsResults(): void
    {
        $data = [
            ['id' => 1, 'name' => 'foo'],
            ['id' => 2, 'name' => 'bar'],
        ];

        $stmt = $this->createStub(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchAll')->willReturn($data);
        $stmt->method('closeCursor')->willReturn(true );
        $stmt->method('setFetchMode')->willReturn(true);

        $pdo = $this->createStub(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->model->pdo = $pdo;

        $result = $this->model->fetchAll('SELECT * FROM table');

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals($data, $result);
    }

    /**
     * @throws Exception
     */
    public function testFetchColumnReturnsValue(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchColumn')->with(0)->willReturn(42);
        $stmt->method('closeCursor')->willReturn(true);

        $pdo = $this->createStub(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->model->pdo = $pdo;

        $result = $this->model->fetchColumn('SELECT COUNT(*) FROM table');
        $this->assertEquals(42, $result);
    }

    /**
     * @throws Exception
     */
    public function testFetchColumnReturnsZeroIfFail(): void
    {
        $stmt = $this->createStub(PDOStatement::class);
        $stmt->method('execute')->willReturn(false);

        $pdo = $this->createStub(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->model->pdo = $pdo;

        $result = $this->model->fetchColumn('SELECT COUNT(*) FROM table');
        $this->assertNull( $result);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function testInitPdoReturnsPdoFromArray(): void
    {
        $pdo = $this->createStub(PDO::class);

        $this->model->initializePDO([ ModelParam::PDO => $pdo] );

        $result = $this->model->pdo ;

        $this->assertSame($pdo, $result);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function testInitPdoReturnsPdoFromContainer(): void
    {
        $container = $this->createMock(Container::class);

        $container->method('has')->with('my_pdo') ->willReturn(true);

        $pdo = $this->createStub(PDO::class);

        $container->method('get')->with('my_pdo')->willReturn($pdo);

        $this->model->initializePDO([ ModelParam::PDO => 'my_pdo'], $container);

        $result = $this->model->pdo ;

        $this->assertSame($pdo, $result);
    }

    /**
     * @throws Exception
     */
    public function testInitializeDefaultFetchModeWithSchema(): void
    {
        $stmt = $this->createMock(PDOStatement::class);

        $this->model->schema = self::class;
        $this->model->deferAssignment = true;

        $stmt->method('setFetchMode')->with($this->callback(function($mode)
        {
                return ($mode & PDO::FETCH_CLASS) === PDO::FETCH_CLASS
                    && ($mode & PDO::FETCH_PROPS_LATE) === PDO::FETCH_PROPS_LATE;
            }), self::class);

        $this->model->initializeDefaultFetchMode($stmt);
    }

    /**
     * @throws Exception
     */
    public function testInitializeDefaultFetchModeWithoutSchema(): void
    {
        $stmt = $this->createMock(PDOStatement::class);

        $this->model->schema = null;

        $stmt->method('setFetchMode')->with(PDO::FETCH_ASSOC);

        $this->model->initializeDefaultFetchMode($stmt);
    }

    /**
     * A PDOTrait host that also composes LoggerTrait so the catch branches'
     * $this->warning() calls resolve (no-op without a logger).
     */
    private function loggerHost(): object
    {
        return new class
        {
            use \oihana\models\pdo\PDOTrait ;
            use \oihana\logging\LoggerTrait ;

            public function alter( mixed $document ): mixed { return $document ; }
        } ;
    }

    private function pdoWithThrowingExecute(): PDO
    {
        $stmt = $this->createStub( PDOStatement::class ) ;
        $stmt->method( 'execute' )->willThrowException( new \PDOException( 'boom' ) ) ;

        $pdo = $this->createStub( PDO::class ) ;
        $pdo->method( 'prepare' )->willReturn( $stmt ) ;
        return $pdo ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function testFetchPrintsDebugBlockOnCliFailure(): void
    {
        $this->model->pdo = $this->pdoWithThrowingExecute() ;

        $this->expectOutputRegex( '/PDOTrait::fetch failed/' ) ;
        $result = $this->model->fetch( 'SELECT 1' , [ 'id' => 1 ] ) ;
        $this->assertNull( $result ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function testFetchRethrowsWhenThrowable(): void
    {
        $this->model->pdo = $this->pdoWithThrowingExecute() ;

        $this->expectException( \PDOException::class ) ;
        $this->model->fetch( 'SELECT 1' , [] , true ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function testFetchAllReturnsEmptyWhenExecuteFails(): void
    {
        $stmt = $this->createStub( PDOStatement::class ) ;
        $stmt->method( 'execute' )->willReturn( false ) ;

        $pdo = $this->createStub( PDO::class ) ;
        $pdo->method( 'prepare' )->willReturn( $stmt ) ;

        $this->model->pdo = $pdo ;
        $this->assertSame( [] , $this->model->fetchAll( 'SELECT 1' ) ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function testFetchAllWarnsAndReturnsEmptyOnFailure(): void
    {
        $host = $this->loggerHost() ;
        $host->pdo = $this->pdoWithThrowingExecute() ;

        $this->assertSame( [] , $host->fetchAll( 'SELECT 1' ) ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function testFetchAllRethrowsWhenThrowable(): void
    {
        $host = $this->loggerHost() ;
        $host->pdo = $this->pdoWithThrowingExecute() ;

        $this->expectException( \PDOException::class ) ;
        $host->fetchAll( 'SELECT 1' , [] , true ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function testFetchAllAsGeneratorYieldsAlteredRows(): void
    {
        $stmt = $this->createStub( PDOStatement::class ) ;
        $stmt->method( 'execute' )->willReturn( true ) ;
        $stmt->method( 'fetch' )->willReturnOnConsecutiveCalls( [ 'id' => 1 ] , [ 'id' => 2 ] , false ) ;

        $pdo = $this->createStub( PDO::class ) ;
        $pdo->method( 'prepare' )->willReturn( $stmt ) ;

        $this->model->pdo = $pdo ;

        $rows = iterator_to_array( $this->model->fetchAllAsGenerator( 'SELECT 1' ) ) ;

        $this->assertCount( 2 , $rows ) ;
        $this->assertSame( 1 , $rows[0]->id ) ;
        $this->assertSame( 2 , $rows[1]->id ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function testFetchAllAsGeneratorIsEmptyWhenNoStatement(): void
    {
        $this->model->pdo = null ;
        $this->assertSame( [] , iterator_to_array( $this->model->fetchAllAsGenerator( 'SELECT 1' ) ) ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function testFetchAllAsGeneratorWarnsOnFailure(): void
    {
        $host = $this->loggerHost() ;
        $host->pdo = $this->pdoWithThrowingExecute() ;

        $this->assertSame( [] , iterator_to_array( $host->fetchAllAsGenerator( 'SELECT 1' ) ) ) ;
    }

    /**
     * @throws \Exception
     */
    public function testFetchColumnReturnsNullWhenNoStatement(): void
    {
        $this->model->pdo = null ;
        $this->assertNull( $this->model->fetchColumn( 'SELECT 1' ) ) ;
    }

    /**
     * @throws \Exception
     */
    public function testFetchColumnWarnsAndReturnsNullOnFailure(): void
    {
        $host = $this->loggerHost() ;
        $host->pdo = $this->pdoWithThrowingExecute() ;

        $this->assertNull( $host->fetchColumn( 'SELECT 1' ) ) ;
    }

    /**
     * @throws \Exception
     */
    public function testFetchColumnArrayReturnsValues(): void
    {
        $stmt = $this->createStub( PDOStatement::class ) ;
        $stmt->method( 'execute' )->willReturn( true ) ;
        $stmt->method( 'fetchAll' )->willReturn( [ 'a' , 'b' ] ) ;

        $pdo = $this->createStub( PDO::class ) ;
        $pdo->method( 'prepare' )->willReturn( $stmt ) ;

        $this->model->pdo = $pdo ;
        $this->assertSame( [ 'a' , 'b' ] , $this->model->fetchColumnArray( 'SELECT 1' ) ) ;
    }

    /**
     * @throws \Exception
     */
    public function testFetchColumnArrayReturnsEmptyWhenNoStatement(): void
    {
        $this->model->pdo = null ;
        $this->assertSame( [] , $this->model->fetchColumnArray( 'SELECT 1' ) ) ;
    }

    /**
     * @throws \Exception
     */
    public function testFetchColumnArrayReturnsEmptyWhenExecuteFails(): void
    {
        $stmt = $this->createStub( PDOStatement::class ) ;
        $stmt->method( 'execute' )->willReturn( false ) ;

        $pdo = $this->createStub( PDO::class ) ;
        $pdo->method( 'prepare' )->willReturn( $stmt ) ;

        $this->model->pdo = $pdo ;
        $this->assertSame( [] , $this->model->fetchColumnArray( 'SELECT 1' ) ) ;
    }

    /**
     * @throws \Exception
     */
    public function testFetchColumnArrayWarnsOnFailure(): void
    {
        $host = $this->loggerHost() ;
        $host->pdo = $this->pdoWithThrowingExecute() ;

        $this->assertSame( [] , $host->fetchColumnArray( 'SELECT 1' ) ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function testFetchAllAsGeneratorIsEmptyWhenExecuteFails(): void
    {
        $stmt = $this->createStub( PDOStatement::class ) ;
        $stmt->method( 'execute' )->willReturn( false ) ;

        $pdo = $this->createStub( PDO::class ) ;
        $pdo->method( 'prepare' )->willReturn( $stmt ) ;

        $this->model->pdo = $pdo ;
        $this->assertSame( [] , iterator_to_array( $this->model->fetchAllAsGenerator( 'SELECT 1' ) ) ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function testFetchAllAsGeneratorRethrowsWhenThrowable(): void
    {
        $this->model->pdo = $this->pdoWithThrowingExecute() ;

        $this->expectException( \PDOException::class ) ;
        iterator_to_array( $this->model->fetchAllAsGenerator( 'SELECT 1' , [] , true ) ) ;
    }

    /**
     * @throws \Exception
     */
    public function testFetchColumnRethrowsWhenThrowable(): void
    {
        $this->model->pdo = $this->pdoWithThrowingExecute() ;

        $this->expectException( \PDOException::class ) ;
        $this->model->fetchColumn( 'SELECT 1' , [] , 0 , true ) ;
    }

    /**
     * @throws \Exception
     */
    public function testFetchColumnArrayRethrowsWhenThrowable(): void
    {
        $this->model->pdo = $this->pdoWithThrowingExecute() ;

        $this->expectException( \PDOException::class ) ;
        $this->model->fetchColumnArray( 'SELECT 1' , [] , true ) ;
    }

    public function testIsConnectedReturnsFalseWithoutPdo(): void
    {
        $this->model->pdo = null ;
        $this->assertFalse( $this->model->isConnected() ) ;
    }

    public function testIsConnectedReturnsTrueWhenConnected(): void
    {
        $pdo = $this->createStub( PDO::class ) ;
        $pdo->method( 'getAttribute' )->willReturn( 'Connection OK' ) ;

        $this->model->pdo = $pdo ;
        $this->assertTrue( $this->model->isConnected() ) ;
    }

    public function testIsConnectedReturnsFalseOnPdoException(): void
    {
        $pdo = $this->createStub( PDO::class ) ;
        $pdo->method( 'getAttribute' )->willThrowException( new \PDOException( 'gone' ) ) ;

        $this->model->pdo = $pdo ;
        $this->assertFalse( $this->model->isConnected() ) ;
    }
}