<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_api_health_endpoint_is_registered_under_api_prefix(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'service' => 'order-service',
            ]);
    }

    public function test_checkout_and_order_endpoints_use_the_service_contracts(): void
    {
        config([
            'services.cart_service.url' => 'http://cart-service:3001',
            'services.product_service.url' => 'http://product-service:3000',
        ]);

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === 'http://cart-service:3001/carts/1') {
                return Http::response([
                    'data' => [
                        'items' => [
                            ['product_id' => 7, 'quantity' => 2],
                        ],
                    ],
                ]);
            }

            if ($request->method() === 'GET' && $request->url() === 'http://product-service:3000/products/7') {
                return Http::response([
                    'data' => [
                        'id' => 7,
                        'name' => 'Keyboard',
                        'price' => '150000.00',
                        'stock' => 10,
                    ],
                ]);
            }

            if ($request->method() === 'DELETE' && $request->url() === 'http://cart-service:3001/carts/1') {
                return Http::response(['message' => 'Keranjang berhasil dikosongkan']);
            }

            return Http::response(['message' => 'Unexpected request'], 500);
        });

        $created = $this->postJson('/api/orders', ['user_id' => 1])
            ->assertCreated()
            ->assertJsonPath('data.user_id', 1)
            ->assertJsonPath('data.total_amount', 300000)
            ->assertJsonPath('data.items.0.product_id', 7)
            ->assertJsonStructure([
                'data' => [
                    'items' => [['created_at', 'updated_at']],
                ],
            ]);

        $orderId = $created->json('data.id');

        $this->getJson("/api/orders/{$orderId}")
            ->assertOk()
            ->assertJsonPath('data.id', $orderId);

        $this->getJson('/api/orders/user/1')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->patchJson("/api/orders/{$orderId}/pay")
            ->assertOk()
            ->assertJsonPath('data.status', 'PAID');

        Http::assertSent(fn (Request $request) => $request->method() === 'GET'
            && $request->url() === 'http://cart-service:3001/carts/1');
        Http::assertSent(fn (Request $request) => $request->method() === 'GET'
            && $request->url() === 'http://product-service:3000/products/7');
        Http::assertSent(fn (Request $request) => $request->method() === 'DELETE'
            && $request->url() === 'http://cart-service:3001/carts/1');
    }

    public function test_missing_order_returns_json_not_found_response(): void
    {
        $this->getJson('/api/orders/999999')
            ->assertNotFound()
            ->assertJsonPath('message', 'Pesanan dengan id 999999 tidak ditemukan');
    }

    public function test_empty_cart_returns_conflict_without_creating_an_order(): void
    {
        config(['services.cart_service.url' => 'http://cart-service:3001']);
        Http::fake([
            'http://cart-service:3001/carts/1' => Http::response([
                'data' => ['items' => []],
            ]),
        ]);

        $this->postJson('/api/orders', ['user_id' => 1])
            ->assertStatus(409)
            ->assertJsonPath('message', 'Keranjang user 1 kosong');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_missing_product_returns_not_found_without_creating_an_order(): void
    {
        config([
            'services.cart_service.url' => 'http://cart-service:3001',
            'services.product_service.url' => 'http://product-service:3000',
        ]);

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === 'http://cart-service:3001/carts/1') {
                return Http::response([
                    'data' => ['items' => [['product_id' => 7, 'quantity' => 1]]],
                ]);
            }

            if ($request->method() === 'GET' && $request->url() === 'http://product-service:3000/products/7') {
                return Http::response(['message' => 'Product tidak ditemukan'], 404);
            }

            return Http::response([], 500);
        });

        $this->postJson('/api/orders', ['user_id' => 1])
            ->assertNotFound()
            ->assertJsonPath(
                'message',
                'Produk dengan id 7 tidak ditemukan di product-service',
            );

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_upstream_http_error_returns_service_unavailable(): void
    {
        config(['services.cart_service.url' => 'http://cart-service:3001']);
        Http::fake([
            'http://cart-service:3001/carts/1' => Http::response(['message' => 'Unavailable'], 503),
        ]);

        $this->postJson('/api/orders', ['user_id' => 1])
            ->assertStatus(503)
            ->assertJsonPath(
                'message',
                'Cart service sedang bermasalah, coba lagi nanti',
            );

        $this->assertDatabaseCount('orders', 0);
    }
}
