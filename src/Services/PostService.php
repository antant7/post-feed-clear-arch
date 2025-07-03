<?php

namespace App\Services;

use App\Repositories\IPostRepository;
use App\Repositories\IUserPostViewRepository;

class PostService implements IPostService
{
    private IPostRepository $postRepository;
    private IUserPostViewRepository $userPostViewRepository;

    public function __construct(IPostRepository $postRepository, IUserPostViewRepository $userPostViewRepository)
    {
        $this->postRepository = $postRepository;
        $this->userPostViewRepository = $userPostViewRepository;
    }

    public function getFeed(int $userId, int $limit, int $offset, string $sortBy, string $sortOrder): array
    {
        if ($limit <= 0 || $limit > 100) $limit = 100;
        if ($offset < 0) $offset = 0;

        $allowedSortFields = ['hotness', 'created_at', 'view_count', 'id'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'hotness';
        }

        $sortOrder = strtoupper($sortOrder);
        if (!in_array($sortOrder, ['ASC', 'DESC'])) {
            $sortOrder = 'DESC';
        }

        return $this->postRepository->getFeedPosts($userId, $limit, $offset, $sortBy, $sortOrder);
    }

    public function markAsViewed(int $userId, int $postId): bool
    {
        $result = $this->userPostViewRepository->markAsViewed($userId, $postId);

        // Если был засчитан просмотр - инкрементим view_count
        if ($result) $this->postRepository->updViewCount($postId);

        return $result;
    }
}