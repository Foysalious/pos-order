<?php namespace App\Services\Customer;

use App\Interfaces\CustomerRepositoryInterface;
use App\Models\Customer;
use App\Services\FileManagers\CdnFileManager;
use App\Services\FileManagers\FileManager;
use App\Traits\ModificationFields;
use App\Traits\ResponseAPI;

class Updater
{
    use ResponseAPI;
    use CdnFileManager, FileManager;
    use ModificationFields;

    private $partner;
    private $email;
    private $phone;
    private $picture;
    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepositoryInterface;
    private $customer;
    private $customerid;

    public function __construct(CustomerRepositoryInterface $customerRepositoryInterface)
    {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    public function setCustomerId($customerid)
    {
        $this->customerid = $customerid;
        return $this;
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

    public function setCustomer(Customer $customer): Updater
    {
        $this->customer = $customer;
        return $this;
    }

    public function makeData(): array
    {
        $data = [];
        if(isset($this->partner))$data['name'] = $this->partner;
        if(isset($this->email))$data['email'] = $this->email;
        if(isset($this->phone))$data['phone'] = $this->phone;
        if(isset($this->picture))$data['pro_pic'] = $this->picture;
        return $data + $this->modificationFields(false, true);
    }

    public function update()
    {
         $this->customerRepositoryInterface->update($this->customer, $this->makeData());

    }
}
