<?php
require_once '../config/database.php';
require_once '../config/cors.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Метод не разрешен']);
    exit;
}


$headers = getallheaders();
$token = $headers['Authorization'] ?? '';

if (empty($token)) {
    http_response_code(401);
    echo json_encode(['error' => 'Токен не предоставлен']);
    exit;
}


if (str_starts_with($token, 'Bearer ')) {
    $token = substr($token, 7);
}


$stmt = $pdo->prepare("SELECT id FROM users WHERE remember_token = :token LIMIT 1");
$stmt->execute(['token' => $token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Недействительный токен']);
    exit;
}


$stmt = $pdo->prepare("SELECT * FROM collections WHERE user_id = :user_id LIMIT 1");
$stmt->execute(['user_id' => $user['id']]);
$collection = $stmt->fetch(PDO::FETCH_ASSOC);

http_response_code(200);
echo json_encode([
    'success' => true,
    'collection' => $collection ?: null
]);
exit;
?>
