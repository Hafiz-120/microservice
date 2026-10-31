<?php

namespace Tests\Feature;

use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ProductServiceUnavailableException;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_a_product_from_product_service(): void
    {
        config([
            'services.product_service.url' => 'http://product-service:3000',
            'services.product_service.timeout' => 5,
        ]);
        Http::fake([
            'http://product-service:3000/products/7' => Http::response([
                'data' => [
                    'id' => 7,
                    'name' => 'Keyboard',
                    'price' => '150000.00',
                    'stock' => 10,
                ],
            ]),
        ]);

        $product = app(ProductService::class)->getProduct(7);

        $this->assertSame(7, $product['id']);
        Http::assertSent(fn (Request $request) => $request->url() === 'http://product-service:3000/products/7');
    }

    public function test_it_throws_product_not_found_for_a_404_response(): void
    {
        config(['services.product_service.url' => 'http://product-service:3000']);
        Http::fake([
            'http://product-service:3000/products/404' => Http::response([], 404),
        ]);

        $this->expectException(ProductNotFoundException::class);

        app(ProductService::class)->getProduct(404);
    }

    public function test_it_surfaces_upstream_http_errors(): void
    {
        config(['services.product_service.url' => 'http://product-service:3000']);
        Http::fake([
            'http://product-service:3000/products/7' => Http::response([], 503),
        ]);

        $this->expectException(ProductServiceUnavailableException::class);

        app(ProductService::class)->getProduct(7);
    }

    public function test_cart_rejects_products_missing_from_the_catalog(): void
    {
        config(['services.product_service.url' => 'http://product-service:3000']);
        Http::fake([
            'http://product-service:3000/products/404' => Http::response([], 404),
        ]);

        $this->postJson('/carts', [
            'user_id' => 1,
            'product_id' => 404,
            'quantity' => 1,
        ])
            ->assertNotFound()
            ->assertJsonPath('message', 'Product dengan id 404 tidak ditemukan');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cart_does_not_change_when_product_service_is_unavailable(): void
    {
        config(['services.product_service.url' => 'http://product-service:3000']);
        Http::fake([
            'http://product-service:3000/products/7' => Http::response([], 503),
        ]);

        $this->postJson('/carts', [
            'user_id' => 1,
            'product_id' => 7,
            'quantity' => 1,
        ])
            ->assertStatus(503)
            ->assertJsonPath(
                'message',
                'Product service tidak tersedia, coba lagi nanti',
            );

        $this->assertDatabaseCount('cart_items', 0);
    }
}
