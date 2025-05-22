<?php
require_once '../config/database.php';
require_once '../config/cors.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? '';

    if (empty($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'Токен не предоставлен']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE remember_token = ?");
    $stmt->execute([$token]);

    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Метод не разрешен']);
    exit;
}
?>