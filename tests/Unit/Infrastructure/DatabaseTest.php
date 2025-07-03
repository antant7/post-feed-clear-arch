<?php

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Database;
use PDO;

class DatabaseTest extends TestCase
{
    /**
     * @covers \App\Infrastructure\Database::getConnection
     */
    public function testGetConnectionReturnsPDOInstance(): void
    {
        // Create a mock Database that doesn't actually connect
        $mockPdo = $this->createMock(PDO::class);
        
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['connect'])
            ->getMock();
            
        // Use reflection to set the private $conn property
        $reflectionClass = new \ReflectionClass(Database::class);
        $connProperty = $reflectionClass->getProperty('conn');
        $connProperty->setAccessible(true);
        $connProperty->setValue($database, $mockPdo);

        // Act
        $result = $database->getConnection();

        // Assert
        $this->assertInstanceOf(PDO::class, $result);
        $this->assertSame($mockPdo, $result);
    }
}