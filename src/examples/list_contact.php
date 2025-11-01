<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\ApiClient;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

$token = is_file(__DIR__ . '/../../d365_token.txt') ? trim(file_get_contents(__DIR__ . '/../../d365_token.txt')) : null;
$client = new ApiClient($token);

$org = rtrim($_ENV['D365_ORG_URL'], '/');
$apiBase = $org.'/api/data/'.($_ENV['D365_API_VER'] ?? 'v9.2').'/';

$resp = $client->get($apiBase.'contacts', [
    '$select' => 'contactid,fullname,emailaddress1,telephone1',
    '$orderby' => 'createdon desc',
    '$top' => 5
]);

header('Content-Type: application/json; charset=utf-8');

if ($resp['ok']) {
    // D365 API ส่ง data กลับมาใน key 'value'
    $contacts = $resp['data']['value'] ?? [];
    echo json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    http_response_code($resp['status']);
    echo json_encode([
        'error' => true,
        'status' => $resp['status'],
        'message' => $resp['error'] ?? 'Request failed',
        'data' => $resp['data']
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}