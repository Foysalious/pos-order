<?php namespace App\Services\Customer;

use App\Interfaces\CustomerRepositoryInterface;
use App\Models\Customer;

use App\Services\Order\Constants\SalesChannels;
use App\Services\Order\Constants\Statuses;
use App\Traits\ResponseAPI;

class Creator
{
    use ResponseAPI;

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

    private function generateImageFrom64base($reviewIndex, $reviewSingleImage, $reviewIndexFromImageName): string
    {
        $randomImageFile = $this->uniqueFileNameFor64base(generateRandomFileName(15)) . '_review_image' . '.png'; // 64 base has no file name. So, we have to create it.
        is_array($reviewSingleImage) ? (file_put_contents($randomImageFile, base64_decode($reviewSingleImage[0]))) : file_put_contents($randomImageFile, base64_decode($reviewSingleImage)); // put that image into local storage
        $reviewImageUrl = $this->saveFileToCDN($randomImageFile, reviewImageFolder(), $randomImageFile);
        unlink($randomImageFile); // remove local image after saving in CDN
        return $reviewImageUrl;
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
