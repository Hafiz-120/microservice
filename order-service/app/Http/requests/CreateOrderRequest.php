<?php
 
namespace App\Http\Requests;
 
class CreateOrderRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'min:1', 'max:999999999'],
        ];
    }
 
    public function messages(): array
    {
        return [
            'user_id.required' => 'Field user_id wajib diisi',
            'user_id.integer' => 'Field user_id harus berupa angka bulat',
            'user_id.min' => 'Field user_id minimal 1',
            'user_id.max' => 'Field user_id terlalu besar',
        ];
    }
}
