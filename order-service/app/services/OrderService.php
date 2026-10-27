<?php
 
namespace App\Services;
 
use App\Clients\CartClient;
use App\Clients\ProductClient;
use App\Exceptions\EmptyCartException;
use App\Exceptions\ExternalServiceException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidOrderStatusException;
use App\Exceptions\OrderNotFoundException;
use App\Exceptions\ProductNotFoundException;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
 
class OrderService
{
    public function __construct(
        private readonly CartClient $cartClient,
        private readonly ProductClient $productClient,
    ) {
    }
 
    public function checkout(int $userId): Order
    {
        // 1. Baca keranjang
        $cartItems = $this->cartClient->getItems($userId);
        if (count($cartItems) === 0) {
            throw new EmptyCartException("Keranjang user {$userId} kosong");
        }
 
        // Gabungkan jika ada product_id yang muncul lebih dari sekali
        $quantities = [];
        foreach ($cartItems as $item) {
            $productId = (int) $item['product_id'];
            $quantities[$productId] = ($quantities[$productId] ?? 0) + (int) $item['quantity'];
        }
 
        // 2-3. Ambil data produk, periksa aturan bisnis, siapkan snapshot
        $lines = [];
        $total = 0;
        foreach ($quantities as $productId => $quantity) {
            $product = $this->productClient->find($productId);
 
            if ($product === null) {
                throw new ProductNotFoundException(
                    "Produk dengan id {$productId} tidak ditemukan di product-service"
                );
            }
 
            $stock = (int) $product['stock'];
            if ($quantity > $stock) {
                throw new InsufficientStockException(
                    "Stok {$product['name']} tidak mencukupi: diminta {$quantity}, tersedia {$stock}"
                );
            }
 
            // Harga dari product-service berupa teks "450000.00" -> angka
            $unitPrice = (int) round((float) $product['price']);
            $subtotal = $unitPrice * $quantity;
 
            $lines[] = [
                'product_id' => $productId,
                'product_name' => $product['name'],
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];
            $total += $subtotal;
        }
 
        // 4. Simpan pesanan + item dalam satu transaksi
        $order = DB::transaction(function () use ($userId, $lines, $total) {
            $order = Order::create([
                'user_id' => $userId,
                'status' => Order::STATUS_PENDING,
                'total_amount' => $total,
            ]);
            $order->items()->createMany($lines);
 
            return $order;
        });
 
        // 5. Kosongkan keranjang. Gagal di sini TIDAK membatalkan pesanan.
        try {
            $this->cartClient->clear($userId);
        } catch (ExternalServiceException $e) {
            Log::warning('Pesanan tersimpan tetapi keranjang gagal dikosongkan', [
                'order_id' => $order->id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
 
        return $order->load('items');
    }
 
    public function find(int $id): Order
    {
        $order = Order::with('items')->find($id);
 
        if ($order === null) {
            throw new OrderNotFoundException("Pesanan dengan id {$id} tidak ditemukan");
        }
 
        return $order;
    }
 
    public function listByUser(int $userId, ?string $status = null): Collection
    {
        return Order::with('items')
            ->where('user_id', $userId)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }
 
    public function pay(int $id): Order
    {
        return DB::transaction(function () use ($id) {
            // lockForUpdate: dua request bayar bersamaan tidak lolos keduanya
            $order = Order::with('items')->lockForUpdate()->find($id);
 
            if ($order === null) {
                throw new OrderNotFoundException("Pesanan dengan id {$id} tidak ditemukan");
            }
 
            if ($order->status !== Order::STATUS_PENDING) {
                throw new InvalidOrderStatusException(
                    "Pesanan berstatus {$order->status}, hanya pesanan PENDING yang dapat dibayar"
                );
            }
 
            $order->status = Order::STATUS_PAID;
            $order->save();
 
            return $order;
        });
    }
}
