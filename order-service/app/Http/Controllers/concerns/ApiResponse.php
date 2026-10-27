<?php
 
namespace App\Http\Controllers\Concerns;
 
use App\Exceptions\ExternalServiceException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;
 
trait ApiResponse
{
    protected function success(string $message, mixed $data, int $status = 200): JsonResponse
    {
        return response()->json(['message' => $message, 'data' => $data], $status);
    }
 
    protected function notFound(Throwable $e): JsonResponse
    {
        return response()->json(['message' => $e->getMessage()], 404);
    }
 
    protected function conflict(Throwable $e, array $context = []): JsonResponse
    {
        Log::warning('Permintaan ditolak oleh aturan bisnis', $context + [
            'reason' => $e->getMessage(),
            'type' => class_basename($e),
        ]);
 
        return response()->json(['message' => $e->getMessage()], 409);
    }
 
    protected function unavailable(ExternalServiceException $e): JsonResponse
    {
        $body = ['message' => $e->getMessage()];
 
        if (config('app.debug')) {
            $body['debug'] = [
                'service' => $e->service,
                'detail' => $e->getPrevious()?->getMessage(),
            ];
        }
 
        return response()->json($body, 503);
    }
 
    protected function serverError(Throwable $e, array $context = []): JsonResponse
    {
        Log::error('Error tak terduga', $context + ['exception' => $e]);
 
        $body = ['message' => 'Terjadi kesalahan pada server'];
 
        if (config('app.debug')) {
            $body['debug'] = [
                'exception' => get_class($e),
                'error' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
            ];
        }
 
        return response()->json($body, 500);
    }
}
