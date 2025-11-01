<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\ApiClient;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

$client = new ApiClient();

$loginPath = $_ENV['API_LOGIN_PATH'] ?? '/api/login';

$res = $client->post($loginPath, [
    'email'    => $_ENV['API_EMAIL'],
    'password' => $_ENV['API_PASSWORD'],
]);

if ($res['ok'] && !empty($res['data']['token'])) {
    // เขียน token ลงไฟล์ token.txt
    file_put_contents(__DIR__ . '/../../token.txt', $res['data']['token']);
    echo "Login success. Token saved to token.txt\n";
} else {
    echo "Login failed: HTTP {$res['status']}\n";
    print_r($res['data']);
}
