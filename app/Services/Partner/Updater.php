<?php namespace App\Services\Partner;

use App\Models\Partner;
use App\Traits\ModificationFields;

class Updater
{
    use ModificationFields;
    protected PartnerUpdateDto $partnerDto;
    protected Partner $partner;


    /**
     * @param PartnerUpdateDto $partnerDto
     */
    public function setPartnerDto(PartnerUpdateDto $partnerDto)
    {
        $this->partnerDto = $partnerDto;
        return $this;
    }

    /**
     * @param Partner $partner
     * @return $this
     */
    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function update()
    {
        $data = $this->makeData();
        $this->partner->update($this->withUpdateModificationField($data));
    }

    private function makeData()
    {
        $data = $this->partnerDto->toArray();
        foreach ($data as $key=>$value) {
            if(is_null($value))
                unset($data[$key]);
        }
        return $data;
    }
}
