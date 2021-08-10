<?php namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderInvoiceResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'invoice' => $this->invoice,
        ];
    }
}
