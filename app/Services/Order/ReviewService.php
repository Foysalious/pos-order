<?php


namespace App\Services\Order;


use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\ReviewRepositoryInterface;
use App\Services\BaseService;
use App\Services\FileManagers\CdnFileManager;
use App\Services\FileManagers\FileManager;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use App\Http\Resources\ReviewResource;

class ReviewService extends BaseService
{
    use FileManager, CdnFileManager;

    protected $reviewRepositoryInterface;
    protected $orderRepositoryInterface;
    protected $reviewCreator;

    public function __construct(ReviewRepositoryInterface $reviewRepositoryInterface, OrderRepositoryInterface $orderRepositoryInterface, ReviewCreator $reviewCreator)
    {
        $this->reviewRepositoryInterface = $reviewRepositoryInterface;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->reviewCreator = $reviewCreator;
    }

    public function getProductReviews($request, $product_id) :object
    {
        list($offset, $limit) = calculatePagination($request);
        $reviews = ReviewResource::collection($this->reviewRepositoryInterface->getReviews($offset, $limit, $product_id,$request->rating,$request->review));
        if(count($reviews) == 0) return $this->error('এই প্রোডাক্ট এর জন্য কোন রিভিউ পাওয়া যায় নি', 404);
        return $this->success('Successful', ['reviews' => $reviews], 200);
    }

    public function create($request, $customer_id, $order_id)
    {
        $order = $this->orderRepositoryInterface->where('customer_id', $customer_id)->find($order_id);
        if(!$order) return $this->error('অর্ডারটি পাওয়া যায় নি', 404);

        file_put_contents('upload.jpg', base64_decode($request[1][0]));
        $this->saveFileToCDN(('upload.jpg'), reviewImageFolder(), 'upload.jpg');
        unlink('upload.jpg');
        //dd(reviewImageFolder());

       // list($file, $fileName) = [$file, $this->uniqueFileName($file, '_' . getFileName($file) . '_review_image')];
      //  $this->saveFileToCDN($file, reviewImageFolder(), $fileName);
        //dd("YES");

        $this->reviewCreator->setOrderId($order_id)
            ->setCustomerId($customer_id)
            ->setPartnerId($request->partner_id)
            ->setReview($request->review)
            ->setReviewImages($request->review_images)
            ->create();

        return $this->success('Successful', null, 200, true);
    }
}
