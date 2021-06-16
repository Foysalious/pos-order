<?php namespace App\Repositories\Accounting;

use App\Models\Partner;
use App\Repositories\Accounting\Constants\UserType;
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
     * @param $request
     * @param $type
     * @param $entry_id
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function updateEntry($request, $type, $entry_id)
    {
        /*
        $this->getCustomer($request);
        $partner = $this->getPartner($request);
        $this->setModifier($partner);
        $data = $this->createEntryData($request, $type, $request->source_id);
        $url = "api/entries/".$entry_id;
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($partner->id)->post($url, $data);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
        */
    }

}
