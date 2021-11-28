<?php namespace App\Services\OrderLog;


use App\Models\OrderLog;
use App\Services\Order\Constants\OrderLogTypes;
use App\Services\Order\Constants\PaymentMethods;
use App\Services\Order\PriceCalculation;
use App\Services\OrderLog\Objects\OrderObject;
use App\Services\Transaction\Constants\TransactionTypes;
use Carbon\Carbon;

class OrderLogGenerator
{
    private OrderLog $log;
    private ?OrderObject $oldObject;
    private ?OrderObject $newObject;

    /**
     * @param OrderLog $log
     * @return OrderLogGenerator
     */
    public function setLog(OrderLog $log): OrderLogGenerator
    {
        $this->log = $log;
        return $this;
    }

    /**
     * @param OrderObject|null $oldObject
     * @return OrderLogGenerator
     */
    public function setOldObject(?OrderObject $oldObject): OrderLogGenerator
    {
        $this->oldObject = $oldObject;
        return $this;
    }

    /**
     * @param OrderObject $newObject
     * @return OrderLogGenerator
     */
    public function setNewObject(OrderObject $newObject): OrderLogGenerator
    {
        $this->newObject = $newObject;
        return $this;
    }

    public function getLogDetails()
    {
        /** @var PriceCalculation $priceCalculation */
        $priceCalculation = app(PriceCalculation::class);
        $old_discounted_price = $this->oldObject ? $priceCalculation->setOrder($this->oldObject)->getDiscountedPrice() : null;
        $new_discounted_price = $this->newObject ? $priceCalculation->setOrder($this->newObject)->getDiscountedPrice() : null;

        if ($this->log->type == OrderLogTypes::DUE_BILL) {
            return [
                'id' => $this->log->id,
                'log_type' => OrderLogTypes::DUE_BILL,
                'log_type_show_name' => ['bn' => 'বাকি বিল', 'en' => 'Due Bill'],
                'old_value' => $old_discounted_price,
                'new_value' => $new_discounted_price,
                'created_at' => convertTimezone(Carbon::parse($this->log->created_at))?->format('Y-m-d H:i:s'),
                'created_by_name' => $this->log->created_by_name,
                'is_invoice_downloadable' => $this->isInvoiceDownloadable('due_bill')
            ];
        } elseif ($this->log->type == OrderLogTypes::EMI) {
            return [
                'id' => $this->log->id,
                'log_type' => OrderLogTypes::EMI,
                'log_type_show_name' => ['bn' => 'কিস্তি - '. $this->newObject->emi_month .'মাস', 'en' => 'Emi - ' . $this->newObject->emi_month .'Months'],
                'old_value' => null,
                'new_value' => $new_discounted_price,
                'created_at' => convertTimezone($this->log->created_at)?->format('Y-m-d H:i:s'),
                'created_by_name' => $this->log->created_by_name,
                'is_invoice_downloadable' => $this->isInvoiceDownloadable(OrderLogTypes::EMI)
            ];
        } elseif ($this->log->type == OrderLogTypes::PAYMENTS) {
            $payment = $this->newObject->payments->last();
            if($payment->transaction_type==TransactionTypes::DEBIT) {
                $log_type_show_name_bn = 'ফেরত';
                $log_type_show_name_en = 'Refund';
            } else {
                if($payment->method==PaymentMethods::CASH_ON_DELIVERY || $payment->method==PaymentMethods::QR_CODE) {
                    $log_type_show_name_bn = 'নগদ  গ্রহণ';
                    $log_type_show_name_en = 'Cash Collection';
                } else {
                    $log_type_show_name_bn = 'অনলাইন গ্রহন';
                    $log_type_show_name_en = 'Online Collection';
                }
            }
            return [
                'id' => $this->log->id,
                'log_type' => OrderLogTypes::PAYMENTS,
                'log_type_show_name' => [
                    'bn' => $log_type_show_name_bn,
                    'en' => $log_type_show_name_en
                ],
                'old_value' => null,
                'new_value' => $payment->amount,
                'created_at' => convertTimezone(Carbon::parse($this->log->created_at))?->format('Y-m-d H:i:s'),
                'created_by_name' => $this->log->created_by_name,
                'is_invoice_downloadable' => $this->isInvoiceDownloadable(OrderLogTypes::PAYMENTS)
            ];
        } elseif ($this->log->type == OrderLogTypes::PRODUCTS_AND_PRICES) {
            return [
                'id' => $this->log->id,
                'log_type' => 'payable',
                'log_type_show_name' => [
                    'bn' => $old_discounted_price <  $new_discounted_price ? 'অর্ডার আপডেট - দাম বেড়েছে' : 'অর্ডার আপডেট - দাম কমেছে',
                    'en' => $old_discounted_price <  $new_discounted_price ? 'Order Update - Price Increased' : 'Order Update - Price Decreased'
                ],
                'old_value' => null,
                'new_value' => $old_discounted_price <  $new_discounted_price ? round($new_discounted_price - $old_discounted_price, 2) : round($old_discounted_price - $new_discounted_price, 2),
                'created_at' => convertTimezone(Carbon::parse($this->log->created_at))?->format('Y-m-d H:i:s'),
                'created_by_name' => $this->log->created_by_name,
                'is_invoice_downloadable' => $this->isInvoiceDownloadable('payable')
            ];
        } elseif ($this->log->type == OrderLogTypes::ORDER_STATUS) {
            return [
                'id' => $this->log->id,
                'log_type' => 'status_update',
                'log_type_show_name' => [
                    'bn' => $this->oldObject->status . ' থেকে '  . $this->newObject->status . ' হয়েছে',
                    'en' => $this->oldObject->status . ' To ' . $this->newObject->status
                ],
                'old_value' => $this->oldObject->status,
                'new_value' =>$this->newObject->status,
                'created_at' => convertTimezone(Carbon::parse($this->log->created_at))?->format('Y-m-d H:i:s'),
                'created_by_name' => $this->log->created_by_name,
                'is_invoice_downloadable' => $this->isInvoiceDownloadable('status_update')
            ];
        } else {
            return null;
        }
    }

    private function isInvoiceDownloadable($log_type): bool
    {
        if ($log_type == 'status_update') return false;
        return true;
    }


}
