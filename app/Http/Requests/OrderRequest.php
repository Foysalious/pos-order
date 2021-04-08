<?php namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
            'products' => 'required|string',
            'paid_amount' => 'sometimes|required|numeric',
            'payment_method' => 'sometimes|required|string|in:' . implode(',', config('pos.payment_method')),
            'customer_name' => 'string',
            'customer_mobile' => 'string',
            'customer_address' => 'string',
            //'nPos' => 'numeric',
            'discount' => 'numeric',
            'is_percentage' => 'numeric',
            'previous_order_id' => 'numeric',
            'emi_month' => 'required_if:payment_method,emi|numeric',
            'amount_without_charge' => 'sometimes|required_if:payment_method,emi|numeric|min:' . config('emi.manager.minimum_emi_amount'),
            'payment_link_amount' => 'sometimes|numeric',
            'sales_channel' => 'sometimes|string'

        ];
    }

}
