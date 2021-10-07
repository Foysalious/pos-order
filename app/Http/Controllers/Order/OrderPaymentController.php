<?php namespace App\Http\Controllers\Order;


use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OnlinePayment\Creator;
use App\Services\OnlinePayment\CreatorDto;
use App\Services\Order\PriceCalculation;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class OrderPaymentController extends Controller
{
    /**
     * @throws UnknownProperties
     * @throws ValidationException
     */
    public function create(Order $order, Request $request, PriceCalculation $priceCalculation,)
    {
        $this->validate($request, [
            'purpose' => 'required',
            'emi_month' => 'sometimes|numeric',
            'interest_paid_by' => 'sometimes|string',
            'transaction_charge' => 'sometimes|numeric'
        ]);
        $creator = new Creator (new CreatorDto ([
            'due' => $priceCalculation->setOrder($order)->getDue(),
            'emi_month' => $request->emi_month,
            'interest_paid_by' => $request->interest_paid_by,
            'transaction_charge' => $request->transaction_charge,
            'purpose' => $request->purpose,
        ]));
        $creator->initiate();
    }
}
