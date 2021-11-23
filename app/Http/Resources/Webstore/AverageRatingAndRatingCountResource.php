<?php

namespace App\Http\Resources\Webstore;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AverageRatingAndRatingCountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'product_id' => $this->product_id,
            'rating_count' => $this->rating_count,
            'avg_rating' => $this->avg_rating
        ];

    }
}
