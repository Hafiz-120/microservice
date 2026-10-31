<?php
 
namespace App\Clients;
 
use App\Exceptions\ExternalServiceException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
 
abstract class BaseServiceClient
{
    protected string $name;       // contoh: product-service
    protected string $configKey;  // contoh: product_service
 
    protected function baseUrl(): string
    {
        return rtrim(config("services.{$this->configKey}.url"), '/');
    }
 
    protected function timeout(): int
    {
        // Batas keras 5 detik sesuai soal
        return min((int) config("services.{$this->configKey}.timeout", 5), 5);
    }
 
    protected function label(): string
    {
        return ucfirst(str_replace('-', ' ', $this->name)); // "Product service"
    }
 
    protected function send(string $method, string $path): Response
    {
        $url = $this->baseUrl() . $path;
 
        try {
            return Http::timeout($this->timeout())
                ->acceptJson()
                ->send($method, $url);
        } catch (ConnectionException $e) {
            Log::error("{$this->name} tidak dapat dihubungi", [
                'service' => $this->name,
                'method' => $method,
                'url' => $url,
                'timeout' => $this->timeout(),
                'error' => $e->getMessage(),
            ]);
 
            throw new ExternalServiceException(
                $this->name,
                $this->label() . ' tidak dapat dihubungi, coba lagi nanti',
                $e
            );
        }
    }
 
    protected function failOnError(Response $response, string $method, string $path): void
    {
        if ($response->failed()) {
            Log::error("{$this->name} membalas error", [
                'service' => $this->name,
                'method' => $method,
                'url' => $this->baseUrl() . $path,
                'status' => $response->status(),
                'body' => Str::limit($response->body(), 300),
            ]);
 
            throw new ExternalServiceException(
                $this->name,
                $this->label() . ' sedang bermasalah, coba lagi nanti'
            );
        }
    }
}
