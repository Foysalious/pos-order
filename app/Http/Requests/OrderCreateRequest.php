<?php namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderCreateRequest extends FormRequest
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
            'customer_id'       => 'sometimes|required',
            'delivery_name'     => 'sometimes|required',
            'delivery_mobile'   => 'sometimes|required',
            'delivery_address'  => 'sometimes|required',
            'delivery_district' => 'sometimes|required',
            'delivery_thana'    => 'sometimes|required',
            'delivery_method'   => 'sometimes|required',
            'total_weight'      => 'sometimes|required',
            'sales_channel_id'  => 'sometimes|required|numeric',
            'paid_amount'       => 'sometimes|required|numeric',
            'payment_method'    => 'sometimes|required|string|in:' . implode(',', config('pos.payment_method')),
            'emi_month'         => 'required_if:payment_method,emi|numeric|gt:0',
            'discount'          => 'sometimes|JSON',
            'voucher_id'        => 'sometimes|numeric',
            'skus'              => 'required|json',
            'delivery_vendor_name' => 'sometimes'

        ];
    }
}
