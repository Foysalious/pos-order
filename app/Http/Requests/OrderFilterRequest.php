<?php

namespace App\Http\Requests;

use App\Services\Order\Constants\Statuses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderFilterRequest extends FormRequest
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
            'customer_name'     => 'sometimes',
            'payment_status'    => Rule::in(Statuses::PAYMENT_STATUS),
            'order_status'      => Rule::in(Statuses::DECLINED, Statuses::CANCELLED, Statuses::COMPLETED, Statuses::SHIPPED, Statuses::PROCESSING, Statuses::PENDING),
            'order_id'          => 'sometimes',
            'sales_channel_id'  => 'sometimes',
            'type'              => Rule::in(Statuses::ORDER_FILTER_TYPE),
            'offset'            => 'numeric',
            'limit'             => 'numeric'
        ];
    }
}
