<?php namespace App\Services\PaymentLink;

use App\Interfaces\PaymentLinkRepositoryInterface;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Partner;
use App\Services\BaseService;

class PaymentLinkService extends BaseService
{
    private Creator $creator;

    public function __construct(Creator $creator)
    {
        $this->creator = $creator;
    }

    private function userStatusCheck($request)
    {
        if (!$request->user) return $this->error('User not found', 404);
        if ($request->user instanceof Partner && in_array($request->user->status, [PartnerStatuses::BLACKLISTED, PartnerStatuses::PAUSED])) {
            return $this->error($request, 401);
        }
        return true;
    }

    private function deActivatePreviousLink(Order $order)
    {
        $payment_link_target = $order->getPaymentLinkTarget();
        $payment_link = app(PaymentLinkRepositoryInterface::class)->getPaymentLinksByPosOrder($payment_link_target);
        $key = $payment_link_target->toString();
        $links = null;
        if (array_key_exists($key, $payment_link))
            $links = $payment_link[$key];
        if ($links) {
            foreach ($links as $link) {
                $this->creator->setStatus('deactivate')->setPaymentLinkId($link->getLinkID())->editStatus();
            }
        }
    }

    public function store($request)
    {
        $userStatusCheck = $this->userStatusCheck($request);
        if ($userStatusCheck !== true) return $userStatusCheck;
        $emi_month_invalidity = Creator::validateEmiMonth($request->all());
        if ($emi_month_invalidity !== false) return $this->error($emi_month_invalidity, 404);
        $partner = Partner::find($request->user);
        $this->creator
            ->setIsDefault($request->isDefault)
            ->setAmount($request->amount)
            ->setReason($request->purpose)
            ->setUserName($partner->name)
            ->setUserId($partner->id)
            ->setUserType($request->type)
            ->setTargetId($request->pos_order_id)
            ->setTargetType('pos_order')
            ->setEmiMonth((int)$request->emi_month)
            ->setPaidBy($request->interest_paid_by)
            ->setTransactionFeePercentage($request->transaction_charge)
            ->calculate();

        if ($request->has('pos_order_id')) {
            $pos_order = Order::find($request->pos_order_id);
            $this->deActivatePreviousLink($pos_order);
            $customer = Customer::find($pos_order->customer_id);
            if (!empty($customer)) $this->creator->setPayerId($customer->id)->setPayerType('pos_customer');
            if ($this->creator->getPaidBy() == 'customer') {
                $pos_order->update(['interest' => $this->creator->getInterest(), 'bank_transaction_charge' => $this->creator->getBankTransactionCharge()]);
            } else {
                $pos_order->update(['interest' => 0, 'bank_transaction_charge' => 0]);
            }

        }
        if ($request->has('customer_id')) {
            $customer = Customer::find($request->customer_id);
            if (!empty($customer)) $this->creator->setPayerId($customer->id)->setPayerType('pos_customer');
        }
        $payment_link_store = $this->creator->create();
        if ($payment_link_store) {
            $payment_link = $this->creator->getPaymentLinkData();

            return $this->success('success', $payment_link, 200);
        }
        return 1;
    }
}
