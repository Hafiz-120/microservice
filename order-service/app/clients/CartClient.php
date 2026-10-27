<?php
 
namespace App\Clients;
 
class CartClient extends BaseServiceClient
{
    protected string $name = 'cart-service';
    protected string $configKey = 'cart_service';
 
    /**
     * Ambil item keranjang user. Keranjang tidak ada (404) dianggap kosong.
     */
    public function getItems(int $userId): array
    {
        $path = "/carts/{$userId}";
        $response = $this->send('GET', $path);
 
        if ($response->status() === 404) {
            return [];
        }
 
        $this->failOnError($response, 'GET', $path);
 
        return $response->json('data.items') ?? [];
    }
 
    /**
     * Kosongkan keranjang user.
     */
    public function clear(int $userId): void
    {
        $path = "/carts/{$userId}";
        $response = $this->send('DELETE', $path);
 
        $this->failOnError($response, 'DELETE', $path);
    }
}
