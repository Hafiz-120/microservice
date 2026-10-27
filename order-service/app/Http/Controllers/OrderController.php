<?php
 
namespace App\Http\Controllers;
 
use App\Exceptions\ExternalServiceException;
use App\Exceptions\OrderConflictException;
use App\Exceptions\OrderNotFoundException;
use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\ListOrdersRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Throwable;
 
class OrderController extends Controller
{
    use ApiResponse;
 
    public function __construct(private readonly OrderService $orderService)
    {
    }
 
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $userId = (int) $request->validated('user_id');
 
        try {
            $order = $this->orderService->checkout($userId);
 
            return $this->success('Pesanan berhasil dibuat', $order, 201);
        } catch (OrderConflictException $e) {
            return $this->conflict($e, ['user_id' => $userId]);
        } catch (ExternalServiceException $e) {
            return $this->unavailable($e);
        } catch (Throwable $e) {
            return $this->serverError($e, ['action' => 'checkout', 'user_id' => $userId]);
        }
    }
 
    public function show(int $id): JsonResponse
    {
        try {
            $order = $this->orderService->find($id);
 
            return $this->success('Berhasil mengambil pesanan', $order);
        } catch (OrderNotFoundException $e) {
            return $this->notFound($e);
        } catch (Throwable $e) {
            return $this->serverError($e, ['action' => 'show', 'order_id' => $id]);
        }
    }
 
    public function indexByUser(ListOrdersRequest $request, int $userId): JsonResponse
    {
        try {
            $orders = $this->orderService->listByUser($userId, $request->validated('status'));
 
            return $this->success('Berhasil mengambil daftar pesanan', $orders);
        } catch (Throwable $e) {
            return $this->serverError($e, ['action' => 'list', 'user_id' => $userId]);
        }
    }
 
    public function pay(int $id): JsonResponse
    {
        try {
            $order = $this->orderService->pay($id);
 
            return $this->success('Pesanan berhasil dibayar', $order);
        } catch (OrderNotFoundException $e) {
            return $this->notFound($e);
        } catch (OrderConflictException $e) {
            return $this->conflict($e, ['order_id' => $id]);
        } catch (Throwable $e) {
            return $this->serverError($e, ['action' => 'pay', 'order_id' => $id]);
        }
    }
}
