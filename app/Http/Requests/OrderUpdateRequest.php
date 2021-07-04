<?php namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'skus' => 'sometimes|string',
            'sales_channel_id' => 'sometimes|string',
            'interest' => 'sometimes|string',
            'delivery_charge' => 'sometimes|numeric',
            'delivery_name' => 'sometimes|string',
            'delivery_address' => 'sometimes|string',
            'note' => 'sometimes|string',
            'delivery_mobile' => 'sometimes|string',
            'voucher_id' => 'sometimes|string',
            'discount' => 'sometimes|JSON',
            'is_percentage' => 'sometimes|numeric',
            'previous_order_id' => 'sometimes|numeric',
            'emi_month' => 'required_if:payment_method,emi|numeric',
            'payment_method' => 'sometimes|required|string|in:' . implode(',', config('pos.payment_method')),
            'amount_without_charge' => 'sometimes|required_if:payment_method,emi|numeric|min:' . config('emi.manager.minimum_emi_amount'),
            'payment_link_amount' => 'sometimes|numeric',
            'paid_amount' => 'sometimes|numeric',
        ];
    }
}
