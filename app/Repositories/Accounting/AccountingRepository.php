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
            app('sentry')->captureException($e);
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param array $data
     * @param int $order_id
     * @param int $partner_id
     * @param string $sourceType
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function updateEntryBySource(array $data, int $order_id, int $partner_id, string $sourceType = 'pos')
    {

        $url = "api/entries/source/" . $sourceType . '/' . $order_id;
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($partner_id)->post($url, $data);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param int $partnerId
     * @param string|int $customerId
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function deleteCustomer(int $partnerId, string|int $customerId)
    {
        $url = "api/due-list/" . $customerId;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($partnerId)->delete($url);
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function deleteEntryBySource(int $partner_id, $source_type, $source_id)
    {
        $url = "api/entries/source/" . $source_type . '/' . $source_id;
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($partner_id)->delete($url);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }
}
