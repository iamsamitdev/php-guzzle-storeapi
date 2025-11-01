<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\ApiClient;

$client = new ApiClient(
    is_file(__DIR__ . '/../../token.txt') ? trim(file_get_contents(__DIR__ . '/../../token.txt')) : null
);

// ปรับฟิลด์ให้ตรงตามโมเดลของ API จริง
$payload = [
    'name'        => 'USB-C Cable 1m',
    'slug'        => 'usb-c-cable-1m',
    'price'       => 149.00,
    'category_id' => 1,
    'description' => 'Durable USB-C cable, 1 meter'
];

$res = $client->post('api/products', $payload);

if ($res['ok']) {
    echo "Created\n";
    print_r($res['data']);
} else {
    echo "Create failed: HTTP {$res['status']}\n";
    print_r($res['data']);
}
