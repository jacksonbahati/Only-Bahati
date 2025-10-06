<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/utils.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/db.php';

final class AuthController {
    public static function register(): void {
        $data = get_json_input();
        require_fields($data, ['name', 'email', 'password']);

        $existing = UserModel::findByEmail($data['email']);
        if ($existing) {
            json_error('Email already registered', 409);
        }

        $userId = UserModel::create($data['name'], $data['email'], $data['password']);
        json_response(['id' => $userId], 201);
    }

    public static function login(): void {
        $data = get_json_input();
        require_fields($data, ['email', 'password']);

        $user = UserModel::verifyCredentials($data['email'], $data['password']);
        if (!$user) {
            json_error('Invalid credentials', 401);
        }

        $token = generate_token();
        $stmt = db()->prepare('INSERT INTO auth_tokens (user_id, token) VALUES (:user_id, :token)');
        $stmt->execute([':user_id' => $user['id'], ':token' => $token]);

        json_response(['token' => $token]);
    }

    public static function me(): void {
        $token = bearer_token();
        if (!$token) {
            json_error('Missing Bearer token', 401);
        }
        $stmt = db()->prepare('SELECT u.id, u.name, u.email, u.role, u.created_at, u.updated_at
                               FROM users u
                               JOIN auth_tokens t ON t.user_id = u.id
                               WHERE t.token = :token LIMIT 1');
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch();
        if (!$user) {
            json_error('Invalid token', 401);
        }
        json_response($user);
    }

    public static function logout(): void {
        $token = bearer_token();
        if (!$token) {
            json_error('Missing Bearer token', 401);
        }
        $stmt = db()->prepare('DELETE FROM auth_tokens WHERE token = :token');
        $stmt->execute([':token' => $token]);
        json_response(['ok' => true]);
    }
}
