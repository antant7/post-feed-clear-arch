<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\PostService;
use App\Services\IPostService;
use App\Repositories\PostRepository;
use App\Repositories\IPostRepository;
use App\Repositories\UserPostViewRepository;
use App\Repositories\IUserPostViewRepository;
use App\Infrastructure\Database;
use App\Infrastructure\IDatabase;

/**
 * Test class autoloading and interface implementations
 */
class AutoloadTest extends TestCase
{
    /**
     * @covers \App\Services\PostService
     * @covers \App\Repositories\PostRepository
     * @covers \App\Repositories\UserPostViewRepository
     * @covers \App\Infrastructure\Database
     */
    public function testCoreClassesExist(): void
    {
        // Assert all core classes exist
        $this->assertTrue(class_exists(PostService::class));
        $this->assertTrue(class_exists(PostRepository::class));
        $this->assertTrue(class_exists(UserPostViewRepository::class));
        $this->assertTrue(class_exists(Database::class));
    }

    /**
     * @covers \App\Services\IPostService
     * @covers \App\Repositories\IPostRepository
     * @covers \App\Repositories\IUserPostViewRepository
     * @covers \App\Infrastructure\IDatabase
     */
    public function testInterfacesExist(): void
    {
        // Assert all interfaces exist
        $this->assertTrue(interface_exists(IPostService::class));
        $this->assertTrue(interface_exists(IPostRepository::class));
        $this->assertTrue(interface_exists(IUserPostViewRepository::class));
        $this->assertTrue(interface_exists(IDatabase::class));
    }

    /**
     * @covers \App\Services\PostService
     * @covers \App\Repositories\PostRepository
     * @covers \App\Repositories\UserPostViewRepository
     * @covers \App\Infrastructure\Database
     */
    public function testClassImplementsInterface(): void
    {
        // Assert classes implement their interfaces
        $this->assertTrue(is_subclass_of(PostService::class, IPostService::class));
        $this->assertTrue(is_subclass_of(PostRepository::class, IPostRepository::class));
        $this->assertTrue(is_subclass_of(UserPostViewRepository::class, IUserPostViewRepository::class));
        $this->assertTrue(is_subclass_of(Database::class, IDatabase::class));
    }

    /**
     * @covers \App\Services\PostService::getFeed
     * @covers \App\Services\PostService::markAsViewed
     */
    public function testPostServiceMethods(): void
    {
        // Assert PostService has required methods
        $this->assertTrue(method_exists(PostService::class, 'getFeed'));
        $this->assertTrue(method_exists(PostService::class, 'markAsViewed'));
    }

    /**
     * @covers \App\Repositories\PostRepository::getFeedPosts
     * @covers \App\Repositories\PostRepository::updViewCount
     */
    public function testPostRepositoryMethods(): void
    {
        // Assert PostRepository has required methods
        $this->assertTrue(method_exists(PostRepository::class, 'getFeedPosts'));
        $this->assertTrue(method_exists(PostRepository::class, 'updViewCount'));
    }

    /**
     * @covers \App\Repositories\UserPostViewRepository::markAsViewed
     */
    public function testUserPostViewRepositoryMethods(): void
    {
        // Assert UserPostViewRepository has required methods
        $this->assertTrue(method_exists(UserPostViewRepository::class, 'markAsViewed'));
    }

    /**
     * @covers \App\Infrastructure\Database::getConnection
     */
    public function testDatabaseMethods(): void
    {
        // Assert Database has required methods
        $this->assertTrue(method_exists(Database::class, 'getConnection'));
    }

    /**
     * @covers \App\Services\PostService::__construct
     */
    public function testPostServiceConstructor(): void
    {
        // Arrange
        $reflectionClass = new \ReflectionClass(PostService::class);
        $constructor = $reflectionClass->getConstructor();
        
        // Assert constructor exists and has correct parameters
        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals(IPostRepository::class, $parameters[0]->getType()->getName());
        $this->assertEquals(IUserPostViewRepository::class, $parameters[1]->getType()->getName());
    }

    /**
     * @covers \App\Repositories\PostRepository::__construct
     */
    public function testPostRepositoryConstructor(): void
    {
        // Arrange
        $reflectionClass = new \ReflectionClass(PostRepository::class);
        $constructor = $reflectionClass->getConstructor();
        
        // Assert constructor exists and has correct parameters
        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals(IDatabase::class, $parameters[0]->getType()->getName());
    }

    /**
     * @covers \App\Repositories\UserPostViewRepository::__construct
     */
    public function testUserPostViewRepositoryConstructor(): void
    {
        // Arrange
        $reflectionClass = new \ReflectionClass(UserPostViewRepository::class);
        $constructor = $reflectionClass->getConstructor();
        
        // Assert constructor exists and has correct parameters
        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals(IDatabase::class, $parameters[0]->getType()->getName());
    }
}