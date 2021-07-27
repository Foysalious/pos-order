<?php namespace App\Services\Partner;

use App\Repositories\PartnerRepository;
use App\Traits\ModificationFields;

class Creator
{
    use ModificationFields;
    protected PartnerDto $partnerDto;

    public function __construct(protected PartnerRepository $partnerRepository)
    {
    }

    /**
     * @param mixed $partner_dto
     */
    public function setPartnerDto(PartnerDto $partner_dto)
    {
        $this->partnerDto = $partner_dto;
        return $this;
    }

    public function create()
    {
        $this->partnerRepository->insert($this->withCreateModificationField($this->partnerDto->toArray()));
    }
}
