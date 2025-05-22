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


    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Неверный формат электронной почты']);
        exit;
    }


    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'Пароль должен быть не менее 6 символов']);
        exit;
    }

    try {

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Адрес указан неверно или существует']);
            exit;
        }


        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $name = explode('@', $email)[0];
        
        $stmt = $pdo->prepare("INSERT INTO users (email, password, name) VALUES (?, ?, ?)");
        $stmt->execute([$email, $password_hash, $name]);
        
        $userId = $pdo->lastInsertId();
        

        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
        $stmt->execute([$token, $userId]);
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $userId,
                'email' => $email,
                'name' => $name
            ],
            'token' => $token
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Регистрация не удалась', 'details' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Метод не разрешен']);
exit;
