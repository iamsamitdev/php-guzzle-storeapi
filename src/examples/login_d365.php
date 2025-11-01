<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\ApiClient;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

$client = new ApiClient();

$tenant = $_ENV['TENANT_ID'];
$clientId = $_ENV['CLIENT_ID'];
$clientSecret = $_ENV['CLIENT_SECRET'];
$org = rtrim($_ENV['D365_ORG_URL'], '/');

// Token endpoint for D365
$tokenUrl = "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token";

// scope ต้องเป็น .default ของ org URL
$scope = $org.'/.default';

// ใช้ postForm() สำหรับ OAuth2 token endpoint
$resp = $client->postForm($tokenUrl, [
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'scope' => $scope,
    'grant_type' => 'client_credentials',
]);

if ($resp['ok'] && !empty($resp['data']['access_token'])) {
    $accessToken = $resp['data']['access_token'];
    
    // บันทึก token ลงไฟล์
    file_put_contents(__DIR__ . '/../../d365_token.txt', $accessToken);
    
    echo "D365 Login success!<br>";
    echo "Token: $accessToken<br>";
    echo "Token saved to d365_token.txt<br>";
} else {
    echo "D365 Login failed: HTTP {$resp['status']}<br>";
    print_r($resp['data']);
}