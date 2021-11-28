<?php namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'delivery_charge' => $this->delivery_charge,
            'qr_code_account_type' => $this->qr_code_account_type,
            'qr_code_image' => $this->qr_code_image
        ];
    }

}
