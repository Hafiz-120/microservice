<?php
 
namespace App\Clients;

use App\Exceptions\ExternalServiceException;
use Illuminate\Support\Facades\Log;

class CartClient extends BaseServiceClient
{
    protected string $name = 'cart-service';
    protected string $configKey = 'cart_service';

    public function getItems(int $userId): array
    {
        $path = "/carts/{$userId}";
        $response = $this->send('GET', $path);
        $this->failOnError($response, 'GET', $path);

        $items = $response->json('data.items');
        if (! is_array($items) || array_values($items) !== $items) {
            Log::error('Respons cart-service tidak sesuai kontrak', [
                'service' => $this->name,
                'path' => $path,
            ]);

            throw new ExternalServiceException($this->name, 'Respons Cart service tidak valid');
        }

        foreach ($items as $item) {
            if (
                ! is_array($item)
                || ! isset($item['product_id'], $item['quantity'])
                || ! is_numeric($item['product_id'])
                || ! is_numeric($item['quantity'])
                || (int) $item['product_id'] < 1
                || (int) $item['quantity'] < 1
            ) {
                Log::error('Item cart-service tidak sesuai kontrak', [
                    'service' => $this->name,
                    'path' => $path,
                ]);

                throw new ExternalServiceException($this->name, 'Respons Cart service tidak valid');
            }
        }

        return $items;
    }

    public function clear(int $userId): void
    {
        $path = "/carts/{$userId}";
        $response = $this->send('DELETE', $path);
        $this->failOnError($response, 'DELETE', $path);
    }
}
