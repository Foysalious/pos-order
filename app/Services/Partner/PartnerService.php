<?php namespace App\Services\Partner;

use App\Constants\ResponseMessages;
use App\Http\Requests\PartnerUpdateRequest;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use App\Repositories\PartnerRepository;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
                'sms_invoice' => $request->sms_invoice ?? 0,
                'auto_printing' => $request->auto_printing ?? 0,
                'printer_name' => $request->printer_name ?? null,
                'printer_model' => $request->printer_model ?? null,
                'qr_code_image'  => $request->qr_code_image ?? null,
                'qr_code_account_type'  => $request->qr_code_account_type ?? null,
                'delivery_charge' => $request->delivery_charge ?? null,
            ]);

        if($partner) {
            $this->partnerUpdater->setPartner($partner)
                ->setPartnerDto($partner_dto)
                ->update();
        }

        return $this->success();
    }


    public function show($partnerId): JsonResponse
    {
        $partner = $this->partnerRepository->where('id', $partnerId)->first();
        if(!$partner)
            return $this->error("Partner is not found", 404);
        $partnerResource = new PartnerResource($partner);
        return $this->success(ResponseMessages::SUCCESS, ['partner' => $partnerResource]);
    }

    public function storeOrGet(Request $request): JsonResponse
    {
        $partner = $this->partnerRepository->where('id', $request->partner_id)->first();
        if(!$partner) {
            $partner = Partner::updateOrCreate(['id' => $request->partner_id],[
                'id' => $request->partner_id,
                'name' => $request->name,
                'sub_domain' => $request->sub_domain,
                'sms_invoice' => $request->sms_invoice ?? 0.0,
                'auto_printing' => $request->auto_printing ?? 0.0,
                'printer_name' => $request->printer_name,
                'printer_model' => $request->printer_model,
                'delivery_charge' => $request->delivery_charge,
                'qr_code_account_type' => $request->qr_code_account_type,
                'qr_code_image' => $request->qr_code_image
            ]);
        }
        $partnerResource = new PartnerResource($partner);
        return $this->success(ResponseMessages::SUCCESS, ['partner' => $partnerResource]);
    }
}
