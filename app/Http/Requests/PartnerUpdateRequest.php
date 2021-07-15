<?php namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class PartnerUpdateRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|string',
            'sub_domain' => 'sometimes|string',
            'sms_invoice' => 'sometimes|boolean',
            'auto_printing' => 'sometimes|boolean',
            'printer_name' => 'sometimes|string',
            'printer_model' => 'sometimes|string',
        ];
    }

}
