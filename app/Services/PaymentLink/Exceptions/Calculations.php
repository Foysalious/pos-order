<?php namespace App\Services\PaymentLink\Exceptions;

class Calculations
{
    public static function breakdownsForManager()
    {
        return config('emi.manager.breakdowns');
    }

    public static function getMonthInterest($month)
    {
        $breakdowns = self::breakdownsForManager();
        $data = array_values(array_filter($breakdowns, function ($item) use ($month) {
            return $item['month'] == $month;
        }));
        return !empty($data) ? $data[0] : [];
    }

    public static function calculateMonthWiseCharge($amount, $month, $interest, $bank_trx_fee = null, $format = true, $partner_profit = 0)
    {
        $rate = ($interest / 100);
        return $format ? [
            "number_of_months" => $month,
            "interest" => "$interest%",
            "total_interest" => number_format(ceil(($amount * $rate))),
            "bank_transaction_fee" => number_format($bank_trx_fee),
            "amount" => number_format(ceil((($amount + ($amount * $rate)) + $bank_trx_fee) / $month)),
            "total_amount" => number_format(($amount + ceil(($amount * $rate))) + $bank_trx_fee),
            "partner_profit" => number_format(round($partner_profit, 2))
        ] : [
            "number_of_months" => $month,
            "interest" => $interest,
            "total_interest" => round(ceil(($amount * $rate)), 2),
            "bank_transaction_fee" => $bank_trx_fee,
            "amount" => round(ceil((($amount + ($amount * $rate)) + $bank_trx_fee) / $month), 1),
            "total_amount" => round(($amount + ceil(($amount * $rate))) + $bank_trx_fee, 2),
            "partner_profit" => round($partner_profit, 2)
        ];
    }

    private static function _getBankTransactionFee($amount, $percentage)
    {
        return ceil($amount * ($percentage / 100));
    }

    public static function getBankTransactionFeeForManager($amount, $percentage = null)
    {
        $fee = self::_getBankTransactionFee($amount, $percentage ?: config('emi.manager.bank_fee_percentage'));
        $partner_profit = $fee - self::_getBankTransactionFee($amount, config('emi.manager.bank_fee_percentage'));
        return [$fee, $partner_profit];
    }

    public static function getMonthData($amount, $month, $format = true, $percentage = null)
    {
        $data = self::getMonthInterest($month);
        $rate = $data['interest'] / 100;
        $bank_trx_fee = self::getBankTransactionFeeForManager($amount + ceil(($amount * $rate)), $percentage);
        return empty($data) ? [] : self::calculateMonthWiseCharge($amount, $data['month'], $data['interest'], $bank_trx_fee[0], $format, $bank_trx_fee[1]);
    }

}
