<?php
declare(strict_types=1);

function json_response(mixed $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function json_error(string $message, int $status = 400, array $meta = []): void {
    json_response(['error' => $message, 'meta' => $meta], $status);
}

function get_json_input(): array {
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        json_error('Invalid JSON payload', 400);
    }
    return $decoded;
}

function require_fields(array $payload, array $required): void {
    foreach ($required as $field) {
        if (!array_key_exists($field, $payload) || $payload[$field] === '' || $payload[$field] === null) {
            json_error("Missing field: {$field}", 422);
        }
    }
}

function hash_password(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

function generate_token(int $length = 32): string {
    return rtrim(strtr(base64_encode(random_bytes($length)), '+/', '-_'), '=');
}

function bearer_token(): ?string {
    $headers = getallheaders();
    if (!is_array($headers)) {
        return null;
    }
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    if (!is_string($auth)) {
        return null;
    }
    if (stripos($auth, 'Bearer ') === 0) {
        return substr($auth, 7);
    }
    return null;
}
