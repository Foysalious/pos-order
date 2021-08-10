<?php namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductWiseReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'order' => 'sometimes|string',
            'orderBy' => 'sometimes|string',
            'from' => 'required|string',
            'to' => 'required|string',
        ];
    }
}
