<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Dotenv\Dotenv;

class ApiClient
{
    private Client $http;
    private string $baseUrl;
    private ?string $token;

    public function __construct(?string $token = null)
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->safeLoad();

        $this->baseUrl = $_ENV['API_BASE_URL'] ?? '';
        $this->token   = $token ?? ($_ENV['API_TOKEN'] ?? null);

        $this->http = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 15,
            'allow_redirects' => true,
            'verify' => false // ปิด SSL verification สำหรับการทดสอบ
        ]);
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    private function headers(array $extra = []): array
    {
        $headers = [
            'Accept'       => '*/*',
            'Content-Type' => 'application/json',
            'User-Agent'   => 'PostmanRuntime/7.42.0',
        ];

        if (!empty($this->token)) {
            $headers['Authorization'] = 'Bearer ' . $this->token;
        }

        return array_merge($headers, $extra);
    }

    /** Generic request helper */
    public function request(string $method, string $path, array $options = []): array
    {
        // ถ้าใช้ form_params ไม่ต้องตั้ง Content-Type: application/json
        // Guzzle จะตั้งเป็น application/x-www-form-urlencoded ให้เองอัตโนมัติ
        if (isset($options['form_params'])) {
            // สำหรับ form data ไม่ใช้ Content-Type: application/json
            $headers = [
                'Accept'       => '*/*',
                'User-Agent'   => 'PostmanRuntime/7.42.0',
            ];
            if (!empty($this->token)) {
                $headers['Authorization'] = 'Bearer ' . $this->token;
            }
            $options['headers'] = array_merge($headers, $options['headers'] ?? []);
        } else {
            // สำหรับ JSON request ปกติ
            $options['headers'] = $this->headers($options['headers'] ?? []);
        }

        try {
            $res = $this->http->request($method, $path, $options);
            $body = (string) $res->getBody();
            return [
                'ok'     => true,
                'status' => $res->getStatusCode(),
                'data'   => $body !== '' ? json_decode($body, true) : null,
            ];
        } catch (RequestException $e) {
            $status = $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;
            $body   = $e->getResponse() ? (string) $e->getResponse()->getBody() : null;
            
            return [
                'ok'     => false,
                'status' => $status,
                'error'  => $e->getMessage(),
                'data'   => $body ? json_decode($body, true) : null,
            ];
        }
    }

    // ---------- Convenience wrappers ----------
    public function get(string $path, array $query = []): array
    {
        return $this->request('GET', $path, ['query' => $query]);
    }

    public function post(string $path, array $json = []): array
    {
        return $this->request('POST', $path, ['json' => $json]);
    }

    public function postForm(string $path, array $formData = []): array
    {
        return $this->request('POST', $path, ['form_params' => $formData]);
    }

    public function put(string $path, array $json = []): array
    {
        return $this->request('PUT', $path, ['json' => $json]);
    }

    public function delete(string $path): array
    {
        return $this->request('DELETE', $path);
    }
}
