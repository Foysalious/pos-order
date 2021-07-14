<?php namespace App\Services\Partner;

use App\Http\Requests\PartnerUpdateRequest;
use App\Repositories\PartnerRepository;
use App\Services\BaseService;

class PartnerService extends  BaseService
{
    public function __construct(
        protected Updater $partnerUpdater,
        protected Creator $partnerCreator,
        protected PartnerRepository $partnerRepository
    )
    {
    }


    public function updatePartner(int $partner_id, PartnerUpdateRequest $request)
    {
        $partner = $this->partnerRepository->where('id', $partner_id)->first();

        $partner_dto = new PartnerDto([
                'id' => $partner_id,
                'name' => $request->name ?? null,
                'sub_domain' => $request->sub_domain ?? null,
                'sms_invoice' => $request->sms_invoice ?? null,
                'auto_printing' => $request->auto_printing ?? null,
                'printer_name' => $request->printer_name ?? null,
                'printer_model' => $request->printer_model ?? null,
            ]);
        if(is_null($partner)) {
            $this->partnerCreator->setPartnerDto($partner_dto)->create();
        } else {
            $this->partnerUpdater->setPartner($partner)
                ->setPartnerDto($partner_dto)
                ->update();
        }

        return $this->success('successful', [], 200);
    }
}
