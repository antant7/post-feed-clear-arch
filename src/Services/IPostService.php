<?php

namespace App\Services;

interface IPostService
{
    public function getFeed(int $userId, int $limit, int $offset, string $sortBy, string $sortOrder): array;

    public function markAsViewed(int $userId, int $postId): bool;
}