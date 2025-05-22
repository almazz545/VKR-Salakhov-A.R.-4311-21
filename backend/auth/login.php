<?php
require_once '../config/database.php';
require_once '../config/cors.php';

setCorsHeaders();

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Введите адрес электронной почты и пароль']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, email, password, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $token = bin2hex(random_bytes(32));
            $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->execute([$token, $user['id']]);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name']
                ],
                'token' => $token
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Неверные учетные данные']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Ошибка входа', 'details' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Метод не разрешен']);
exit;