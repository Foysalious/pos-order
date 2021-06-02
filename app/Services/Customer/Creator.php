<?php namespace App\Services\Customer;

use App\Interfaces\CustomerRepositoryInterface;
use App\Models\Customer;
use App\Services\FileManagers\CdnFileManager;
use App\Services\FileManagers\FileManager;
use App\Traits\ResponseAPI;

class Creator
{
    use ResponseAPI;
    use CdnFileManager, FileManager;

    private $partner;
    private $email;
    private $phone;
    private $picture;
    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepositoryInterface;

    public function __construct(CustomerRepositoryInterface $customerRepositoryInterface)
    {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    public function setProfilePicture($picture)
    {
        $this->picture = $picture;
        return $this;
    }

    public function create()
    {
        $customer_data['name'] = $this->partner;
        $customer_data['email'] = $this->email;
        $customer_data['phone'] = $this->phone;
        $customer_data['pro_pic'] = $this->picture;
        $this->customer = $this->customerRepositoryInterface->create($customer_data);
        return $this->success('Successful', ['order' => $this->customer], 200);
    }


}
