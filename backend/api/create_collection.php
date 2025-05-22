<?php
require_once '../config/database.php';
require_once '../config/cors.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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


$data = json_decode(file_get_contents('php://input'), true);
$title = $data['title'] ?? '';
$description = $data['description'] ?? '';
$goal = $data['goal'] ?? 0;

if (empty($title) || empty($description) || $goal <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Все поля (title, description, goal) обязательны']);
    exit;
}


$stmt = $pdo->prepare("INSERT INTO collections (user_id, title, description, goal, collected, order_total) VALUES (:user_id, :title, :description, :goal, 0, :order_total)");
$stmt->execute([
    'user_id' => $user['id'],
    'title' => $title,
    'description' => $description,
    'goal' => $goal,
    'order_total' => generateRandomOrderTotal() 
]);

function generateRandomOrderTotal() {
    return rand(400, 8000); 
}

$collectionId = $pdo->lastInsertId();

http_response_code(201);
echo json_encode([
    'success' => true,
    'collection' => [
        'id' => $collectionId,
        'user_id' => $user['id'],
        'title' => $title,
        'description' => $description,
        'goal' => $goal,
        'collected' => 0
    ]
]);
exit;
?>