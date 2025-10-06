<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/utils.php';

final class UserModel {
    public static function create(string $name, string $email, string $password, string $role = 'student'): int {
        $sql = 'INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)';
        $stmt = db()->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password_hash' => hash_password($password),
            ':role' => $role,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function findByEmail(string $email): ?array {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findById(int $id): ?array {
        $stmt = db()->prepare('SELECT id, name, email, role, created_at, updated_at FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function verifyCredentials(string $email, string $password): ?array {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        if (!$user) {
            return null;
        }
        if (!verify_password($password, $user['password_hash'])) {
            return null;
        }
        return $user;
    }

    public static function updateProfile(int $id, array $fields): bool {
        $allowed = ['name', 'email', 'role'];
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
        $sql = 'UPDATE users SET ' . implode(', ', $set) . ', updated_at = NOW() WHERE id = :id';
        $stmt = db()->prepare($sql);
        return $stmt->execute($params);
    }
}
