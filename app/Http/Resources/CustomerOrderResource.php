<?php namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Order $this */
        return [
            'id'                      => $this->id,
            'status'                  => $this->status,
            'date'                    => $this->created_at->format('d,M,Y'),
            'price'                   =>$this->calculate()->netBill
        ];
    }
}
