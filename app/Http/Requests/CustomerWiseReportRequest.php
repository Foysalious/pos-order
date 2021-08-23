<?php namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerWiseReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'from' => 'required|string',
            'to' => 'required|string',
        ];
    }
}
