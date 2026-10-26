<?php

namespace App\services;

use illuminate\Support\Facades\Http;
use illuminate\Support\Facades\Log;
use throwable;

class productService
{
    public function getProduct(int $productId): array
    {
        try {
            $response = Http::get(config('services.product.base_url'))
            >get("/products/{$productId}");

            if ($response->successful() && isset($response->json()['data'])) {
                return $response->json()['data'];
            }

            return null;
        } catch (Throwable $e) {
            log::error('Gagal mengambil data product',[
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}