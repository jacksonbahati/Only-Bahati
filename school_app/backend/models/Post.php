<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

final class PostModel {
    public static function create(int $userId, string $title, string $content): int {
        $stmt = db()->prepare('INSERT INTO posts (user_id, title, content) VALUES (:user_id, :title, :content)');
        $stmt->execute([':user_id' => $userId, ':title' => $title, ':content' => $content]);
        return (int) db()->lastInsertId();
    }

    public static function findById(int $id): ?array {
        $stmt = db()->prepare('SELECT p.id, p.user_id, p.title, p.content, p.created_at, p.updated_at, u.name AS author_name
                               FROM posts p JOIN users u ON u.id = p.user_id WHERE p.id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function list(int $limit = 50, int $offset = 0): array {
        $stmt = db()->prepare('SELECT p.id, p.user_id, p.title, p.content, p.created_at, p.updated_at, u.name AS author_name
                               FROM posts p JOIN users u ON u.id = p.user_id
                               ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function update(int $id, array $fields): bool {
        $allowed = ['title', 'content'];
        $set = [];
        $params = [':id' => $id];
        foreach ($fields as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $set[] = "$key = :$key";
                $params[":" . $key] = $value;
            }
        }
        if (empty($set)) {
            return false;
        }
        $sql = 'UPDATE posts SET ' . implode(', ', $set) . ', updated_at = NOW() WHERE id = :id';
        $stmt = db()->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete(int $id): bool {
        $stmt = db()->prepare('DELETE FROM posts WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
