<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/utils.php';
require_once __DIR__ . '/../models/User.php';

final class UserController {
    public static function profile(int $id): void {
        $user = UserModel::findById($id);
        if (!$user) {
            json_error('User not found', 404);
        }
        json_response($user);
    }

    public static function update(int $id): void {
        $data = get_json_input();
        $ok = UserModel::updateProfile($id, $data);
        if (!$ok) {
            json_error('Nothing to update or invalid fields', 400);
        }
        json_response(['ok' => true]);
    }
}
