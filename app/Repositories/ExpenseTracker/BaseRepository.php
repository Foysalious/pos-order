<?php namespace App\Repositories\ExpenseTracker;


use App\Models\Partner;
use App\Services\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use App\Services\ExpenseTracker\ExpenseTrackerClient;
use App\Traits\ModificationFields;

class BaseRepository
{
    use ModificationFields;

    /** @var ExpenseTrackerClient $client */
    protected $client;

    /** @var int $accountId */
    protected $accountId;

    protected $partnerId;

    /**
     * BaseRepository constructor.
     * @param ExpenseTrackerClient $client
     */
    public function __construct(ExpenseTrackerClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param Partner
     * @return $this
     * @throws ExpenseTrackingServerError
     */
    public function setPartner(Partner $partner)
    {
        if (!$partner->expense_account_id) {
            $this->setModifier($partner);
            $data = ['account_holder_type' => get_class($partner), 'account_holder_id' => $partner->id];
            $result = $this->client->post('accounts', $data);
            $data = ['expense_account_id' => $result['account']['id']];
//            $partner->update($data);
        }
        $this->accountId = $partner->expense_account_id;
        $this->accountId = $data['expense_account_id'];
        $this->partnerId = $partner->id;
        return $this;
    }
}
