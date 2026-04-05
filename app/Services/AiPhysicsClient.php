<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AiPhysicsClient
{
    public function simulate(array $payload): array
    {
        return $this->post('/simulate', $payload);
    }

    public function predict(array $payload): array
    {
        return $this->post('/predict', $payload);
    }

    public function datasetBatch(array $payload): array
    {
        return $this->post('/dataset/batch', $payload);
    }

    protected function post(string $path, array $payload): array
    {
        $base = rtrim(config('services.physics_ai.url'), '/');
        $token = config('services.physics_ai.token');
        $timeout = config('services.physics_ai.timeout', 15);

        $req = Http::timeout($timeout)->acceptJson()->asJson();
        if ($token) {
            $req = $req->withHeaders(['X-Service-Token' => $token]);
        }

        try {
            $response = $req->post($base.$path, $payload);
            $response->throw();
        } catch (RequestException $e) {
            Log::warning('physics_ai.request_failed', [
                'path' => $path,
                'message' => $e->getMessage(),
            ]);
            throw new RuntimeException('Physics AI service unavailable.', previous: $e);
        }

        return $response->json();
    }

    public function isConfigured(): bool
    {
        return (bool) config('services.physics_ai.url');
    }
}
