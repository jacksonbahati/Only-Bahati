<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/utils.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../config/db.php';

final class PostController {
    public static function list(): void {
        $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 50;
        $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
        json_response(PostModel::list($limit, $offset));
    }

    public static function get(int $id): void {
        $post = PostModel::findById($id);
        if (!$post) {
            json_error('Post not found', 404);
        }
        json_response($post);
    }

    public static function create(): void {
        $data = get_json_input();
        require_fields($data, ['user_id', 'title', 'content']);
        $id = PostModel::create((int)$data['user_id'], $data['title'], $data['content']);
        json_response(['id' => $id], 201);
    }

    public static function update(int $id): void {
        $data = get_json_input();
        $ok = PostModel::update($id, $data);
        if (!$ok) {
            json_error('Nothing to update or invalid fields', 400);
        }
        json_response(['ok' => true]);
    }

    public static function delete(int $id): void {
        $ok = PostModel::delete($id);
        if (!$ok) {
            json_error('Post not found', 404);
        }
        json_response(['ok' => true]);
    }
}
