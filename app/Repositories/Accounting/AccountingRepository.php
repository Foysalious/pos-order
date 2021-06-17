<?php namespace App\Repositories\Accounting;

use App\Models\Partner;
use App\Repositories\Accounting\Constants\EntryTypes;
use App\Repositories\Accounting\Constants\UserType;
use App\Services\Accounting\Constants\Sales;
use App\Services\Accounting\Exceptions\AccountingEntryServerError;
use Carbon\Carbon;

class AccountingRepository extends BaseRepository
{
    /**
     * @param int $partner_id
     * @param array $data
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function storeEntry(int $partner_id, array $data)
    {
        $url = "api/entries/";
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($partner_id)->post($url, $data);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param Request $request
     * @param $sourceId
     * @param $sourceType
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function updateEntryBySource(array $data, int $order_id, int $partner_id, $sourceType='pos')
    {

        $url = "api/entries/source/" . $sourceType . '/' . $order_id;
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($partner_id)->post($url, $data);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

}
