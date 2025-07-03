<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\PostService;
use App\Repositories\IPostRepository;
use App\Repositories\IUserPostViewRepository;

class PostServiceTest extends TestCase
{
    private PostService $postService;
    private IPostRepository $mockPostRepository;
    private IUserPostViewRepository $mockUserPostViewRepository;

    protected function setUp(): void
    {
        $this->mockPostRepository = $this->createMock(IPostRepository::class);
        $this->mockUserPostViewRepository = $this->createMock(IUserPostViewRepository::class);
        $this->postService = new PostService($this->mockPostRepository, $this->mockUserPostViewRepository);
    }

    /**
     * @covers \App\Services\PostService::getFeed
     */
    public function testGetFeed(): void
    {
        // Arrange
        $userId = 1;
        $inputLimit = 10;
        $inputOffset = 0;
        $inputSortBy = 'hotness';
        $inputSortOrder = 'DESC';
        $expectedResult = [
            ['id' => 1, 'title' => 'Test Post 1', 'content' => 'Content 1'],
            ['id' => 2, 'title' => 'Test Post 2', 'content' => 'Content 2']
        ];
        
        $this->mockPostRepository
            ->expects($this->once())
            ->method('getFeedPosts')
            ->with($userId, $inputLimit, $inputOffset, $inputSortBy, $inputSortOrder)
            ->willReturn($expectedResult);

        // Act
        $result = $this->postService->getFeed($userId, $inputLimit, $inputOffset, $inputSortBy, $inputSortOrder);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers \App\Services\PostService::markAsViewed
     */
    public function testMarkAsViewed(): void
    {
        // Arrange
        $userId = 1;
        $postId = 100;
        $markAsViewedResult = true;
        $expectedResult = true;
        
        $this->mockUserPostViewRepository
            ->expects($this->once())
            ->method('markAsViewed')
            ->with($userId, $postId)
            ->willReturn($markAsViewedResult);

        $this->mockPostRepository
            ->expects($this->once())
            ->method('updViewCount')
            ->with($postId);

        // Act
        $result = $this->postService->markAsViewed($userId, $postId);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers \App\Services\PostService::markAsViewed
     */
    public function testMarkAsViewedFailure(): void
    {
        // Arrange
        $userId = 1;
        $postId = 100;
        $markAsViewedResult = false;
        $expectedResult = false;
        
        $this->mockUserPostViewRepository
            ->expects($this->once())
            ->method('markAsViewed')
            ->with($userId, $postId)
            ->willReturn($markAsViewedResult);

        $this->mockPostRepository
            ->expects($this->never())
            ->method('updViewCount');

        // Act
        $result = $this->postService->markAsViewed($userId, $postId);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers \App\Services\PostService::getFeed
     */
    public function testGetFeedWithLimitValidation(): void
    {
        // Arrange
        $userId = 1;
        $inputLimit = 150; // Too high
        $inputOffset = 0;
        $inputSortBy = 'hotness';
        $inputSortOrder = 'DESC';
        $expectedLimit = 100; // Should be capped
        $expectedResult = [];
        
        $this->mockPostRepository
            ->expects($this->once())
            ->method('getFeedPosts')
            ->with($userId, $expectedLimit, $inputOffset, $inputSortBy, $inputSortOrder)
            ->willReturn($expectedResult);

        // Act
        $result = $this->postService->getFeed($userId, $inputLimit, $inputOffset, $inputSortBy, $inputSortOrder);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }
}