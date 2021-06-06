<?php namespace App\Services\Customer;

use App\Interfaces\CustomerRepositoryInterface;
use App\Services\BaseService;

class CustomerService extends BaseService
{
    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;
    /**
     * @var Updater
     */
    private Updater $updater;


    public function __construct(CustomerRepositoryInterface $customerRepository, Updater $updater)
    {
        $this->updater = $updater;
        $this->customerRepository = $customerRepository;
    }

    public function update($request, int $customer_id)
    {
        $customerDetails = $this->customerRepository->find($customer_id);
        if (!$customerDetails) return $this->error('Customer Not Found', 404);
        $this->updater->setPartner($request->name)->setEmail($request->email)->setPhone($request->phone)->setProfilePicture($request->picture)->setCustomer($customerDetails)->setCustomerId($customerDetails->id)->update();

    }
}
