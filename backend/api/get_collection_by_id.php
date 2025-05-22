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

$id = $_GET['id'] ?? 0;

if (empty($id)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID сбора обязателен']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM collections WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$collection = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$collection) {
    http_response_code(404);
    echo json_encode(['error' => 'Сбор завершен']);
    exit;
}

http_response_code(200);
echo json_encode([
    'success' => true,
    'collection' => $collection
]);
exit;
?>