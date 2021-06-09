<?php namespace App\Services\Order\Payment;


use App\Interfaces\OrderPaymentRepositoryInterface;
use App\Traits\ModificationFields;

class Creator
{
    use ModificationFields;
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
        $this->orderPaymentRepository->insert($this->withCreateModificationField($data));
    }

}
