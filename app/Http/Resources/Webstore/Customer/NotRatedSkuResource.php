<?php namespace App\Http\Resources\Webstore\Customer;

use Illuminate\Http\Resources\Json\JsonResource;


class NotRatedSkuResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'order_id' => $this->order_id,
            'details' => json_decode($this->details,true)
        ];
    }
}
