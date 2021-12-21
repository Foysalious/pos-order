<?php namespace App\Services\Customer;

use App\Interfaces\CustomerRepositoryInterface;
use App\Models\Customer;
use App\Services\ClientServer\SmanagerUser\SmanagerUserServerClient;
use App\Traits\ModificationFields;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerResolver
{
    use ModificationFields;

    private string $customerId;
    private int $partnerId;

    public function __construct(
        protected CustomerRepositoryInterface $customerRepository,
        protected SmanagerUserServerClient    $smanagerUserServerClient
    )
    {
    }

    /**
     * @param string $customerId
     * @return CustomerResolver
     */
    public function setCustomerId(string $customerId)
    {
        $this->customerId = $customerId;
        return $this;
    }

    /**
     * @param int $partnerId
     * @return CustomerResolver
     */
    public function setPartnerId(int $partnerId)
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    public function resolveCustomer(): Customer
    {
        $customer = $this->customerRepository->where('id', $this->customerId)->where('partner_id', $this->partnerId)->first();
        if (!$customer) {
            $customer = $this->getCustomerFromSmanagerUserProject();
            if (!$customer) throw new NotFoundHttpException(trans('order.customer_not_found'));
            $data = [
                'id' => $customer['_id'],
                'name' => $customer['name'],
                'email' => $customer['email'],
                'partner_id' => $customer['partner_id'],
                'mobile' => $customer['mobile'],
                'pro_pic' => $customer['pro_pic'],
            ];
            return $this->customerRepository->builder()
                ->updateOrCreate(['id' => $data['id'], 'partner_id' => $data['partner_id']], $this->withCreateModificationField($data));
        }
        return $customer;
    }

    private function getCustomerFromSmanagerUserProject()
    {
        return $this->smanagerUserServerClient->get('api/v1/partners/' . $this->partnerId . '/users/' . $this->customerId);
    }
}
