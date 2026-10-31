<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Validation\Rule;

class ListOrdersRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in([Order::STATUS_PENDING, Order::STATUS_PAID])],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status harus PENDING atau PAID',
        ];
    }
}
