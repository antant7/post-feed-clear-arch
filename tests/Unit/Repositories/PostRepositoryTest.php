<?php

namespace Tests\Unit\Repositories;

use PHPUnit\Framework\TestCase;
use App\Repositories\PostRepository;
use App\Infrastructure\IDatabase;
use PDO;
use PDOStatement;

class PostRepositoryTest extends TestCase
{
    private PostRepository $postRepository;
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
            
        $this->postRepository = new PostRepository($this->mockDatabase);
    }

    /**
     * @covers \App\Repositories\PostRepository::getFeedPosts
     */
    public function testGetFeedPosts(): void
    {
        // Arrange
        $userId = 1;
        $limit = 10;
        $offset = 0;
        $orderBy = 'hotness';
        $sortOrder = 'DESC';
        $expectedResult = [
            [
                'id' => 1,
                'title' => 'Test Post 1',
                'content' => 'Content 1',
                'hotness' => 100,
                'created_at' => '2024-01-01 10:00:00',
                'view_count' => 50
            ]
        ];
        
        $expectedSql = "
            SELECT p.id, p.title, p.content, p.hotness, p.created_at, p.view_count FROM posts p
            WHERE NOT EXISTS (
                SELECT 1 FROM user_post_views uv WHERE uv.user_id = :user_id AND uv.post_id = p.id
            )
            AND view_count < 1000 ORDER BY $orderBy $sortOrder
            LIMIT :limit OFFSET :offset
        ";

        $this->mockPdo
            ->expects($this->once())
            ->method('prepare')
            ->with($expectedSql)
            ->willReturn($this->mockStatement);

        $this->mockStatement
            ->expects($this->exactly(3))
            ->method('bindValue')
            ->willReturn(true);

        $this->mockStatement
            ->expects($this->once())
            ->method('execute');

        $this->mockStatement
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedResult);

        // Act
        $result = $this->postRepository->getFeedPosts($userId, $limit, $offset, $orderBy, $sortOrder);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers \App\Repositories\PostRepository::updViewCount
     */
    public function testUpdViewCount(): void
    {
        // Arrange
        $postId = 1;
        $expectedSql = "
            UPDATE posts SET view_count = view_count + 1 WHERE id = :post_id
        ";

        $this->mockPdo
            ->expects($this->once())
            ->method('prepare')
            ->with($expectedSql)
            ->willReturn($this->mockStatement);

        $this->mockStatement
            ->expects($this->once())
            ->method('bindValue')
            ->with(':post_id', $postId, PDO::PARAM_INT)
            ->willReturn(true);

        $this->mockStatement
            ->expects($this->once())
            ->method('execute');

        // Act
        $this->postRepository->updViewCount($postId);

        // Assert - method returns void, so we just verify the calls were made
        $this->assertTrue(true);
    }

    /**
     * @covers \App\Repositories\PostRepository::getFeedPosts
     */
    public function testGetFeedPostsWithDifferentSorting(): void
    {
        // Arrange
        $userId = 2;
        $limit = 20;
        $offset = 5;
        $orderBy = 'created_at';
        $sortOrder = 'ASC';
        $expectedResult = [];
        
        $expectedSql = "
            SELECT p.id, p.title, p.content, p.hotness, p.created_at, p.view_count FROM posts p
            WHERE NOT EXISTS (
                SELECT 1 FROM user_post_views uv WHERE uv.user_id = :user_id AND uv.post_id = p.id
            )
            AND view_count < 1000 ORDER BY $orderBy $sortOrder
            LIMIT :limit OFFSET :offset
        ";

        $this->mockPdo
            ->expects($this->once())
            ->method('prepare')
            ->with($expectedSql)
            ->willReturn($this->mockStatement);

        $this->mockStatement
            ->expects($this->exactly(3))
            ->method('bindValue')
            ->willReturn(true);

        $this->mockStatement
            ->expects($this->once())
            ->method('execute');

        $this->mockStatement
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedResult);

        // Act
        $result = $this->postRepository->getFeedPosts($userId, $limit, $offset, $orderBy, $sortOrder);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers \App\Repositories\PostRepository::getFeedPosts
     */
    public function testGetFeedPostsEmptyResult(): void
    {
        // Arrange
        $userId = 1;
        $limit = 10;
        $offset = 100;
        $orderBy = 'hotness';
        $sortOrder = 'DESC';
        $expectedResult = [];
        
        $expectedSql = "
            SELECT p.id, p.title, p.content, p.hotness, p.created_at, p.view_count FROM posts p
            WHERE NOT EXISTS (
                SELECT 1 FROM user_post_views uv WHERE uv.user_id = :user_id AND uv.post_id = p.id
            )
            AND view_count < 1000 ORDER BY $orderBy $sortOrder
            LIMIT :limit OFFSET :offset
        ";

        $this->mockPdo
            ->expects($this->once())
            ->method('prepare')
            ->with($expectedSql)
            ->willReturn($this->mockStatement);

        $this->mockStatement
            ->expects($this->exactly(3))
            ->method('bindValue')
            ->willReturn(true);

        $this->mockStatement
            ->expects($this->once())
            ->method('execute');

        $this->mockStatement
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedResult);

        // Act
        $result = $this->postRepository->getFeedPosts($userId, $limit, $offset, $orderBy, $sortOrder);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }
}