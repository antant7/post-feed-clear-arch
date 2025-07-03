<?php

namespace Tests\Unit\Repositories;

use PHPUnit\Framework\TestCase;
use App\Repositories\UserPostViewRepository;
use App\Infrastructure\IDatabase;
use PDO;
use PDOStatement;

class UserPostViewRepositoryTest extends TestCase
{
    private UserPostViewRepository $userPostViewRepository;
    private IDatabase $mockDatabase;
    private PDO $mockPdo;
    private PDOStatement $mockStatement;

    protected function setUp(): void
    {
        $this->mockDatabase = $this->createMock(IDatabase::class);
        $this->mockPdo = $this->createMock(PDO::class);
        $this->mockStatement = $this->createMock(PDOStatement::class);
        
        $this->mockDatabase
            ->method('getConnection')
            ->willReturn($this->mockPdo);
            
        $this->userPostViewRepository = new UserPostViewRepository($this->mockDatabase);
    }

    /**
     * @covers \App\Repositories\UserPostViewRepository::markAsViewed
     */
    public function testMarkAsViewed(): void
    {
        // Arrange
        $userId = 1;
        $postId = 100;
        $rowCount = 1;
        $expectedResult = true;
        
        $expectedSql = "
        INSERT INTO user_post_views (user_id, post_id) 
        SELECT :user_id, :post_id WHERE EXISTS (
            SELECT 1 FROM posts WHERE id = :post_id
        )
        ON CONFLICT (user_id, post_id) DO NOTHING;
        ";

        $this->mockPdo
            ->expects($this->once())
            ->method('prepare')
            ->with($expectedSql)
            ->willReturn($this->mockStatement);

        $this->mockStatement
            ->expects($this->exactly(2))
            ->method('bindValue')
            ->willReturn(true);

        $this->mockStatement
            ->expects($this->once())
            ->method('execute');

        $this->mockStatement
            ->expects($this->once())
            ->method('rowCount')
            ->willReturn($rowCount);

        // Act
        $result = $this->userPostViewRepository->markAsViewed($userId, $postId);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers \App\Repositories\UserPostViewRepository::markAsViewed
     */
    public function testMarkAsViewedDuplicate(): void
    {
        // Arrange
        $userId = 1;
        $postId = 100;
        $rowCount = 0; // No rows affected - duplicate
        $expectedResult = false;
        
        $expectedSql = "
        INSERT INTO user_post_views (user_id, post_id) 
        SELECT :user_id, :post_id WHERE EXISTS (
            SELECT 1 FROM posts WHERE id = :post_id
        )
        ON CONFLICT (user_id, post_id) DO NOTHING;
        ";

        $this->mockPdo
            ->expects($this->once())
            ->method('prepare')
            ->with($expectedSql)
            ->willReturn($this->mockStatement);

        $this->mockStatement
            ->expects($this->exactly(2))
            ->method('bindValue')
            ->willReturn(true);

        $this->mockStatement
            ->expects($this->once())
            ->method('execute');

        $this->mockStatement
            ->expects($this->once())
            ->method('rowCount')
            ->willReturn($rowCount);

        // Act
        $result = $this->userPostViewRepository->markAsViewed($userId, $postId);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers \App\Repositories\UserPostViewRepository::markAsViewed
     */
    public function testMarkAsViewedNonExistentPost(): void
    {
        // Arrange
        $userId = 1;
        $postId = 999999; // Non-existent post
        $rowCount = 0;
        $expectedResult = false;
        
        $expectedSql = "
        INSERT INTO user_post_views (user_id, post_id) 
        SELECT :user_id, :post_id WHERE EXISTS (
            SELECT 1 FROM posts WHERE id = :post_id
        )
        ON CONFLICT (user_id, post_id) DO NOTHING;
        ";

        $this->mockPdo
            ->expects($this->once())
            ->method('prepare')
            ->with($expectedSql)
            ->willReturn($this->mockStatement);

        $this->mockStatement
            ->expects($this->exactly(2))
            ->method('bindValue')
            ->willReturn(true);

        $this->mockStatement
            ->expects($this->once())
            ->method('execute');

        $this->mockStatement
            ->expects($this->once())
            ->method('rowCount')
            ->willReturn($rowCount);

        // Act
        $result = $this->userPostViewRepository->markAsViewed($userId, $postId);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }
}