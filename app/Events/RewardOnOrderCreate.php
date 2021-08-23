<?php namespace App\Events;

use App\Models\BaseModel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RewardOnOrderCreate
{
    use Dispatchable, SerializesModels;

    /** @var BaseModel */
    public $model;

    /**
     * Create a new event instance.
     *
     * @param  BaseModel $model
     * @return void
     */
    public function __construct(BaseModel $model)
    {
        $this->model = $model;
    }

}
