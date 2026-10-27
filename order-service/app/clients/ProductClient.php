<?php
 
namespace App\Clients;
 
use App\Exceptions\ExternalServiceException;
use Illuminate\Support\Facades\Log;
 
class ProductClient extends BaseServiceClient
{
    protected string $name = 'product-service';
    protected string $configKey = 'product_service';
 
    /**
     * Ambil produk. Mengembalikan null jika product-service membalas 404.
     */
    public function find(int $id): ?array
    {
        $path = "/products/{$id}";
        $response = $this->send('GET', $path);
 
        if ($response->status() === 404) {
            return null;
        }
 
        $this->failOnError($response, 'GET', $path);
 
        $data = $response->json('data');
        if (! is_array($data)) {
            Log::error('Respons product-service tidak sesuai kontrak', [
                'service' => $this->name, 'path' => $path,
            ]);
            throw new ExternalServiceException($this->name, 'Respons Product service tidak valid');
        }
 
        return $data;
    }
}
