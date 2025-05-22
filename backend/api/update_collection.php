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


$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? 0;
$title = $data['title'] ?? '';
$description = $data['description'] ?? '';
$goal = $data['goal'] ?? 0;
$collected = $data['collected'] ?? 0;

if (empty($id) || empty($title) || empty($description) || $goal <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Все поля (id, title, description, goal) обязательны']);
    exit;
}


$stmt = $pdo->prepare("UPDATE collections SET title = :title, description = :description, goal = :goal, collected = :collected WHERE id = :id");
$stmt->execute([
    'title' => $title,
    'description' => $description,
    'goal' => $goal,
    'collected' => $collected,
    'id' => $id,
]);

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Коллекция не найдена']);
    exit;
}

http_response_code(200);
echo json_encode([
    'success' => true,
    'collection' => [
        'id' => $id,
        'title' => $title,
        'description' => $description,
        'goal' => $goal,
        'collected' => $collected
    ]
]);
exit;
?>