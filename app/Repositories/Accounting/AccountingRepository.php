<?php namespace App\Repositories\Accounting;

use App\Models\EventNotification;
use App\Models\Order;
use App\Repositories\Accounting\Constants\UserType;
use App\Services\Accounting\Exceptions\AccountingEntryServerError;
use App\Services\ClientServer\Exceptions\BaseClientServerError;
use App\Services\EventNotification\Events;
use App\Services\EventNotification\Services;

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
        $url = "api/entries/";
        return $this->client->setUserType(UserType::PARTNER)->setUserId($partner_id)
            ->setEventNotification($this->createEventNotification(Events::ORDER_CREATE))
            ->post($url, $data);
    }

    /**
     * @param array $data
     * @param int $order_id
     * @param int $partner_id
     * @param string $sourceType
     * @return mixed
     * @throws AccountingEntryServerError
     * @throws \Exception
     */
    public function updateEntryBySource(array $data, int $order_id, int $partner_id, string $sourceType = 'pos'): mixed
    {
        $url = "api/entries/source/" . $sourceType . '/' . $order_id;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($partner_id)
            ->setEventNotification($this->createEventNotification(Events::ORDER_UPDATE))
            ->post($url, $data);
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function deleteEntryBySource($source_type,)
    {
        $url = "api/entries/source/" . $source_type . '/' . $this->order->id;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($this->order->partner_id)
            ->setEventNotification($this->createEventNotification(Events::ORDER_DELETE))
            ->delete($url);
    }

    /**
     * @throws \Exception
     */
    private function createEventNotification(string $eventName): EventNotification
    {
        if (!$this->order) throw new \Exception("Order is not set for accounting hit");
        $event_notification = new EventNotification();
        $event_notification->order_id = $this->order->id;
        $event_notification->event = $eventName;
        $event_notification->service = Services::ACCOUNTING;
        $event_notification->save();
        return $event_notification;
    }
}
