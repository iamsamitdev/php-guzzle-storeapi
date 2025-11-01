<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\ApiClient;

$token = is_file(__DIR__ . '/../../token.txt') ? trim(file_get_contents(__DIR__ . '/../../token.txt')) : null;
$client = new ApiClient($token);

// สมมุติ endpoint แสดงสินค้าคือ /api/products
$page   = (int)($_GET['page'] ?? 1);
$search = $_GET['search'] ?? null;

$query = ['page' => $page];
if (!empty($search)) {
    $query['search'] = $search;
}

$res = $client->get('api/products', $query);

header('Content-Type: application/json; charset=utf-8');

if ($res['ok']) {
    echo json_encode($res['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    http_response_code($res['status']);
    echo json_encode([
        'error' => true,
        'status' => $res['status'],
        'message' => $res['error'] ?? 'Request failed',
        'data' => $res['data']
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
