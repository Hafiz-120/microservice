<?php

namespace App\Services;

use App\Exceptions\CartItemNotFoundException;
use App\Exceptions\CartStockExceededException;
use App\Models\CartItem;

class CartService
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    public function getCart(int $userId): array
    {
        $items = CartItem::forUser($userId)->get();

        return [
            'user_id' => $userId,
            'items' => $items,
            'total_quantity' => $items->sum('quantity')
        ];
    }

    public function addItem(int $userId, int $productId, int $quantity): CartItem
    {
        $product = $this->productService->getProduct($productId);

        $item = CartItem::firstOrNew([
            'user_id' => $userId,
            'product_id' => $productId
        ]);

        $newQuantity = ($item->exists ? $item->quantity : 0) + $quantity;
        $this->ensureQuantityAvailable($newQuantity, (int) $product['stock']);

        $item->quantity = $newQuantity;

        $item->save();

        return $item;
    }

    // update keranjang
    public function updateQuantity(int $id, int $quantity): CartItem
    {
        $item = CartItem::find($id);

        if (!$item) {
            throw new CartItemNotFoundException($id);
        }

        $product = $this->productService->getProduct($item->product_id);
        $this->ensureQuantityAvailable($quantity, (int) $product['stock']);

        $item->update(['quantity' => $quantity]);

        return $item;
    }

    private function ensureQuantityAvailable(int $quantity, int $stock): void
    {
        if ($quantity > $stock) {
            throw new CartStockExceededException($stock);
        }
    }


    // Hapus	satu	item.	Melempar	exception	kalau	id	tidak	ditemukan
    public function removeItem(int $id)
    {
        if (CartItem::destroy($id) === 0) {
            throw new CartItemNotFoundException($id);
        }
    }

    // Kosongkan	keranjang	satu	user.	Balikin	jumlah	item	yang	dihapus
    public function clearCart(int $userId)
    {
        return CartItem::where('user_id', $userId)->delete();
    }
}