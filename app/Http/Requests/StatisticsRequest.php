<?php namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatisticsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'frequency' => 'required|string|in:day,week,month,quarter,year',
            'date' => 'required_if:frequency,day,quarter|date',
            'week' => 'required_if:frequency,week|numeric',
            'month' => 'required_if:frequency,month|numeric',
            'year' => 'required_if:frequency,month,year|numeric',
        ];
    }
}
