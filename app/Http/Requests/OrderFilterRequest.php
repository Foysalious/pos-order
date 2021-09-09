<?php namespace App\Http\Requests;

use App\Services\Order\Constants\OrderTypes;
use App\Services\Order\Constants\PaymentStatuses;
use App\Services\Order\Constants\Statuses;
use App\Services\Order\OrderFilter;
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
            'payment_status'    => Rule::in(PaymentStatuses::PAID, PaymentStatuses::DUE),
            'order_status'      => Rule::in(Statuses::DECLINED, Statuses::CANCELLED, Statuses::COMPLETED, Statuses::SHIPPED, Statuses::PROCESSING, Statuses::PENDING),
            'order_id'          => 'sometimes|required',
            'sales_channel_id'  => 'sometimes|required',
            'type'              => Rule::in(OrderTypes::COMPLETED, OrderTypes::NEW, OrderTypes::RUNNING),
            'offset'            => 'numeric',
            'limit'             => 'numeric',
            'q'                 => 'sometimes|string',
            'sort_by'           => Rule::in(OrderFilter::SORT_BY_CREATED_AT, OrderFilter::SORT_BY_PRICE, OrderFilter::SORT_BY_CUSTOMER_NAME),
            'sort_by_order'     => Rule::in(OrderFilter::SORT_BY_ASC,OrderFilter::SORT_BY_DESC)
        ];
    }
}
