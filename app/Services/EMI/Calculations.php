<?php namespace App\Services\EMI;


class Calculations
{
    public static function calculateEmiCharges($amount)
    {
        return self::_calculate($amount, config('emi.breakdowns'), self::getBankTransactionFee($amount));
    }

    public static function calculateEmiChargesForManager($amount)
    {
        return self::_calculate($amount, self::breakdownsForManager(), self::getBankTransactionFeeForManager($amount));
    }

    private static function _calculate($amount, $breakdowns, $bank_trx_fee)
    {
        $emi        = [];
        foreach ($breakdowns as $item) {
            array_push($emi, self::calculateMonthWiseCharge($amount, $item['month'], $item['interest'], $bank_trx_fee));
        }
        return $emi;
    }

    public static function calculateMonthWiseCharge($amount, $month, $interest, $bank_trx_fee = null, $format = true)
    {
        $rate                 = ($interest / 100);
        return $format ? [
            "number_of_months"     => $month,
            "interest"             => "$interest%",
            "total_interest"       => number_format(ceil(($amount * $rate))),
            "bank_transaction_fee" => number_format($bank_trx_fee),
            "amount"               => number_format(ceil((($amount + ($amount * $rate)) + $bank_trx_fee) / $month)),
            "total_amount"         => number_format(($amount + ceil(($amount * $rate))) + $bank_trx_fee)
        ] : [
            "number_of_months"     => $month,
            "interest"             => $interest,
            "total_interest"       => ceil(($amount * $rate)),
            "bank_transaction_fee" => $bank_trx_fee,
            "amount"               => ceil((($amount + ($amount * $rate)) + $bank_trx_fee) / $month),
            "total_amount"         => ($amount + ceil(($amount * $rate))) + $bank_trx_fee
        ];
    }

    public static function breakdownsForManager()
    {
        return config('emi.manager.breakdowns');
    }

    private static function _getBankTransactionFee($amount, $percentage)
    {
        return ceil($amount * ($percentage / 100));
    }

    public static function getBankTransactionFee($amount)
    {
        return self::_getBankTransactionFee($amount, config('emi.bank_fee_percentage'));
    }

    public static function getBankTransactionFeeForManager($amount)
    {
        return self::_getBankTransactionFee($amount, config('emi.manager.bank_fee_percentage'));
    }

    public static function getMonthData($amount, $month, $format=true)
    {
        $data = self::getMonthInterest($month);
        $rate = ($data['interest'] / 100);
        $bank_trx_fee = self::getBankTransactionFeeForManager($amount + ceil(($amount * $rate)));

        return empty($data) ? [] : self::calculateMonthWiseCharge($amount, $data['month'], $data['interest'], $bank_trx_fee, $format);
    }

    public static function getMonthInterest($month)
    {
        $breakdowns = self::breakdownsForManager();
        $data       = array_values(array_filter($breakdowns, function ($item) use ($month) {
            return $item['month'] == $month;
        }));
        return !empty($data) ? $data[0] : [];
    }


}
