<?php namespace App\Repositories\Accounting;

use App\Models\Order;
use App\Repositories\Accounting\Constants\UserType;
use App\Services\Accounting\AccountingEntryClient;
use App\Services\ClientServer\Exceptions\BaseClientServerError;

class AccountingRepository extends BaseRepository
{
    private Order $order;

    /**
     * @param Order $order
     * @return AccountingRepository
     */
    public function setOrder(Order $order): AccountingRepository
    {
        $this->order = $order;
        return $this;
    }


    /**
     * @param int $partner_id
     * @param array $data
     * @return mixed
     * @throws BaseClientServerError
     */
    public function storeEntry(int $partner_id, array $data): mixed
    {
        return $this->getClient()->setUserType(UserType::PARTNER)->setUserId($partner_id)->post("api/entries/", $data);
    }

    /**
     * @param array $data
     * @param int $order_id
     * @param int $partner_id
     * @param string $sourceType
     * @return mixed
     * @throws \Exception
     */
    public function updateEntryBySource(array $data, int $order_id, int $partner_id, string $sourceType = 'pos'): mixed
    {
        $url = "api/entries/source/" . $sourceType . '/' . $order_id;
        return $this->getClient()->setUserType(UserType::PARTNER)->setUserId($partner_id)
            ->post($url, $data);
    }

    /**
     * @throws BaseClientServerError
     */
    public function deleteEntryBySource($source_type,): object|array|string|null
    {
        $url = "api/entries/source/" . $source_type . '/' . $this->order->id;
        return $this->getClient()->setUserType(UserType::PARTNER)->setUserId($this->order->partner_id)
            ->delete($url);
    }

    private function getClient(): AccountingEntryClient
    {
        return $this->client->setEventNotification($this->eventNotification);
    }

}
