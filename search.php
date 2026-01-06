<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Только POST-запросы']);
    exit;
}

$nickname = trim($_POST['nickname'] ?? '');
if (!$nickname) {
    echo json_encode(['error' => 'Никнейм не указан']);
    exit;
}

// URL Roblox API
$url = 'https://users.roblox.com/v1/users/search?keyword=' . urlencode($nickname) . '&limit=10';

// Настройка cURL
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_USERAGENT => 'RobloxSearchTool/1.0 (PHP)',
    CURLOPT_HTTPHEADER => [
        'Accept: application/json'
    ],
    // Если у вас ограничения — можно попробовать прокси (ниже комментарий)
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Обработка ошибок cURL
if ($curlError) {
    // Часто: "Could not resolve host" — значит, users.roblox.com заблокирован
    error_log("cURL Error: $curlError");
    echo json_encode(['error' => "Не удалось подключиться к Roblox API (cURL: $curlError). Проверьте интернет или используйте прокси."]);
    exit;
}

if ($httpCode !== 200) {
    error_log("HTTP $httpCode from Roblox: $response");
    echo json_encode(['error' => "Roblox API вернул ошибку: $httpCode"]);
    exit;
}

// Парсим ответ
$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Ошибка парсинга ответа от Roblox']);
    exit;
}

// Передаём только нужное клиенту
echo json_encode([
    'users' => $data['data'] ?? []
]);
?>
