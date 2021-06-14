<?php namespace App\Repositories\Accounting;

use App\Models\Partner;
use App\Services\Accounting\Exceptions\AccountingEntryServerError;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccountingRepository extends BaseRepository
{
    /**
     * @param $request
     * @param $type
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function storeEntry($request, $type)
    {
        $this->getCustomer($request);
        $partner = $this->getPartner($request);
        $this->setModifier($partner);
        $data = $this->createEntryData($request, $type, $request->source_id);
        $url = "api/entries/";
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($partner->id)->post($url, $data);
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
    }


    /**
     * @param Request $request
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function getAccountsTotal(Request $request)
    {
        list($start, $end) = IncomeExpenseStatics::createDataForAccountsTotal($request->start_date, $request->end_date);
        $url = "api/reports/account-list-with-sum/{$request->account_type}?start_date=$start&end_date=$end";
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($request->partner->id)->get($url);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param $services
     * @param $requestedService
     * @return false|string
     */
    public function getInventoryProducts($services, $requestedService)
    {
        $requested_service = json_decode($requestedService, true);
        $inventory_products = [];
        foreach ($services as $key => $service) {
            $original_service = ($service->service);
            if ($original_service) {
                $sellingPrice = isset($requested_service[$key]['updated_price']) && $requested_service[$key]['updated_price'] ? $requested_service[$key]['updated_price'] : $original_service->price;
                $unitPrice = $original_service->cost ?: $sellingPrice;
                $inventory_products[] = [
                    "id" => $original_service->id ?? $requested_service[$key]['id'],
                    "name" => $original_service->name ?? $requested_service[$key]['name'],
                    "unit_price" => (double)$unitPrice,
                    "selling_price" => (double)$sellingPrice,
                    "quantity" => isset($requested_service[$key]['quantity']) ? $requested_service[$key]['quantity'] : 1
                ];
            } else {
                $sellingPrice = isset($requested_service[$key]['updated_price']) ? $requested_service[$key]['updated_price'] : $original_service->price;
                $inventory_products[] = [
                    "id" =>  0,
                    "name" => 'Custom Amount',
                    "unit_price" => $sellingPrice,
                    "selling_price" => isset($original_service->cost) ? $original_service->cost : $sellingPrice,
                    "quantity" => isset($requested_service[$key]['quantity']) ? $requested_service[$key]['quantity'] : 1
                ];
            }

        }
        if (count($inventory_products) > 0) {
            return json_encode($inventory_products);
        }
        return null;
    }


    /**
     * @param Request $request
     * @param $sourceId
     * @param $sourceType
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function updateEntryBySource(Request $request, $sourceId, $sourceType)
    {
        $this->getCustomer($request);
        $this->setModifier($request->partner);
        $data = $this->createEntryData($request, $sourceType, $sourceId);
        $url = "api/entries/source/" . $sourceType . '/' . $sourceId;
        try {
            Log::info(['pos order update data', $data, $request->refund_nature, $request->return_nature]);
            return $this->client->setUserType(UserType::PARTNER)->setUserId($request->partner->id)->post($url, $data);
        } catch (AccountingEntryServerError $e) {
            Log::info(['error from accounting']);
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param Partner $partner
     * @param $sourceType
     * @param $sourceId
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function deleteEntryBySource(Partner $partner, $sourceType, $sourceId)
    {
        $url = "api/entries/source/" . $sourceType . '/' . $sourceId;
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($partner->id)->delete($url);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param $request
     * @param $type
     * @param null $type_id
     * @param bool $default
     * @return array
     */
    private function createEntryData($request, $type, $type_id = null, $default = true): array
    {
        $data['created_from'] = json_encode($this->withBothModificationFields((new RequestIdentification())->get()));
        $data['amount'] = (double)$request->amount;
        $data['source_type'] = $type;
        $data['source_id'] = $type_id;
        $data['note'] = $request->has("note") ? $request->note : null;
        $data['amount_cleared'] = $request->amount_cleared;
        if(!$default) {
            $data['debit_account_key'] = $request->from_account_key; // to = debit = je account e jabe
            $data['credit_account_key'] = $request->to_account_key; // from = credit = je account theke jabe
//            $data['debit_account_key'] = $request->to_account_key;
//            $data['credit_account_key'] = $request->from_account_key;
        } else {
            $data['debit_account_key'] = $request->to_account_key;
            $data['credit_account_key'] = $request->from_account_key;
//            $data['debit_account_key'] = $request->from_account_key; // to = debit = je account e jabe
//            $data['credit_account_key'] = $request->to_account_key; // from = credit = je account theke jabe
        }
        $data['customer_id'] = $request->customer_id;
        $data['customer_name'] = $request->customer_name;
        $data['inventory_products'] = $request->inventory_products;
        $data['entry_at'] = $request->has("date") ? $request->date : Carbon::now()->format('Y-m-d H:i:s');
        $data['attachments'] = $this->uploadAttachments($request);
        $data['total_discount'] = $request->has("total_discount") ? (double)$request->total_discount : null;
        $data['total_vat'] = (double)$request->total_vat;
        return $data;
    }

    private function getPartner($request)
    {
        if(isset($request->partner->id)) {
            $partner_id = $request->partner->id;
        } else {
            $partner_id = (int) $request->partner;
        }
        return Partner::find($partner_id);
    }
}
