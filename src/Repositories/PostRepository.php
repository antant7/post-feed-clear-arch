<?php

namespace App\Repositories;

use App\Infrastructure\IDatabase;
use PDO;

class PostRepository implements IPostRepository {
    private PDO $pdo;
    
    public function __construct(IDatabase $database) {
        $this->pdo = $database->getConnection();
    }
    
    public function getFeedPosts($userId, $limit, $offset, string $orderBy, string $sortOrder): array
    {
        // Переменные $orderBy + $sortOrder прошли валидацию в PostService - строгий контроль от инъекций если метод будет еще откуда-то вызываться
        // Делать здесь логику валидации $orderBy + $sortOrder не соответствует принципам ЧС
        $sql = "
            SELECT p.id, p.title, p.content, p.hotness, p.created_at, p.view_count FROM posts p
            WHERE NOT EXISTS (
                SELECT 1 FROM user_post_views uv WHERE uv.user_id = :user_id AND uv.post_id = p.id
            )
            AND view_count < 1000 ORDER BY $orderBy $sortOrder
            LIMIT :limit OFFSET :offset
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function updViewCount($postId): void
    {
        $sql = "
            UPDATE posts SET view_count = view_count + 1 WHERE id = :post_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
        $stmt->execute();

    }
}