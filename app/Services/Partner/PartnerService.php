<?php namespace App\Services\Partner;

use App\Http\Requests\PartnerUpdateRequest;
use App\Repositories\PartnerRepository;
use App\Services\BaseService;

class PartnerService extends  BaseService
{
    public function __construct(
        protected Updater $partnerUpdater,
        protected PartnerRepository $partnerRepository
    )
    {
    }


    public function updatePartner(int $partner_id, PartnerUpdateRequest $request)
    {
        $partner = $this->partnerRepository->where('id', $partner_id)->first();
        if(!$partner) {
            return $this->error("Bad Request", 400);
        }
        $updateDto = new PartnerUpdateDto([
                'name' => $request->name ?? null,
                'sub_domain' => $request->sub_domain ?? null,
                'sms_invoice' => $request->sms_invoice ?? null,
                'auto_printing' => $request->auto_printing ?? null,
                'printer_name' => $request->printer_name ?? null,
                'printer_model' => $request->printer_model ?? null,
            ]);
        $this->partnerUpdater->setPartner($partner)
            ->setPartnerDto($updateDto)
            ->update();
        return $this->success('successful', [], 200);
    }
}
