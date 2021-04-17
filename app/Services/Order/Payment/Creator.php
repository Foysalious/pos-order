<?php namespace App\Services\Order\Payment;


use App\Interfaces\OrderPaymentRepositoryInterface;

class Creator
{
    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $orderPaymentRepository;

    public function __construct(OrderPaymentRepositoryInterface $orderPaymentRepository)
    {
        $this->orderPaymentRepository = $orderPaymentRepository;
    }

    public function credit(array $data)
    {
        $data['transaction_type'] = 'Credit';
        $this->create($data);
    }

    public function debit(array $data)
    {
        $data['transaction_type'] = 'Debit';
        $this->create($data);
    }

    private function create(array $data)
    {
        $this->orderPaymentRepository->insert($data);
    }

}
