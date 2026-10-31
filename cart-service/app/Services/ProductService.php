<?php

namespace App\Services;

use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ProductServiceUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductService
{
    public function getProduct(int $productId): array
    {
        $baseUrl = rtrim((string) config('services.product_service.url'), '/');
        $timeout = min(5, max(1, (int) config('services.product_service.timeout', 5)));
        $url = "{$baseUrl}/products/{$productId}";

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->get($url);
        } catch (ConnectionException $e) {
            Log::error('Product service tidak dapat dihubungi', [
                'product_id' => $productId,
                'url' => $url,
                'timeout' => $timeout,
                'error' => $e->getMessage(),
            ]);

            throw new ProductServiceUnavailableException(
                'Product service tidak dapat dihubungi, coba lagi nanti',
                $e,
            );
        }

        if ($response->status() === 404) {
            throw new ProductNotFoundException($productId);
        }

        if ($response->failed()) {
            Log::error('Product service membalas error', [
                'product_id' => $productId,
                'url' => $url,
                'status' => $response->status(),
            ]);

            throw new ProductServiceUnavailableException(
                "Product service membalas HTTP {$response->status()}",
            );
        }

        $product = $response->json('data');
        if (
            ! is_array($product)
            || ! isset($product['id'], $product['name'], $product['price'], $product['stock'])
            || ! is_numeric($product['id'])
            || ! is_string($product['name'])
            || ! is_numeric($product['price'])
            || ! is_numeric($product['stock'])
        ) {
            Log::error('Respons product-service tidak sesuai kontrak', [
                'product_id' => $productId,
                'url' => $url,
            ]);

            throw new ProductServiceUnavailableException('Respons product-service tidak valid');
        }

        return $product;
    }
}