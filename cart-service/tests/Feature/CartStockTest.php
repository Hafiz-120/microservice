<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CartStockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.product_service.url' => 'http://product-service:3000',
            'services.product_service.timeout' => 5,
        ]);
        Http::fake([
            'http://product-service:3000/products/*' => Http::response([
                'data' => [
                    'id' => 7,
                    'name' => 'Keyboard',
                    'price' => '150000.00',
                    'stock' => 5,
                ],
            ]),
        ]);
    }

    public function test_it_rejects_adding_more_than_the_remaining_stock(): void
    {
        $this->postJson('/carts', [
            'user_id' => 1,
            'product_id' => 7,
            'quantity' => 3,
        ])->assertCreated();

        $this->postJson('/carts', [
            'user_id' => 1,
            'product_id' => 7,
            'quantity' => 3,
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Jumlah melebihi stok yang tersedia (5)');

        $this->assertDatabaseHas('cart_items', [
            'user_id' => 1,
            'product_id' => 7,
            'quantity' => 3,
        ]);
    }

    public function test_it_rejects_updating_an_item_above_available_stock(): void
    {
        $this->postJson('/carts', [
            'user_id' => 1,
            'product_id' => 7,
            'quantity' => 3,
        ])->assertCreated();

        $itemId = (int) $this->getJson('/carts/1')->json('data.items.0.id');

        $this->putJson("/carts/items/{$itemId}", ['quantity' => 6])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Jumlah melebihi stok yang tersedia (5)');

        $this->assertDatabaseHas('cart_items', [
            'id' => $itemId,
            'quantity' => 3,
        ]);
    }
}
