<?php

namespace App\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWebhookRequest extends FormRequest
{
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
            'webhook' => 'required',
            'application' => 'string',
            'content' => 'string'
        ];
    }
}
