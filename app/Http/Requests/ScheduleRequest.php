<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'start-date' => 'date_format:Y-m-d',
            'start-time' => 'date_format:H:i:s',
            'interval' => 'date_format:H:i:s',
            'file-name' => 'nullable',
            'disabled' => 'required'
        ];
    }
}
