<?php

namespace App\Http\Requests;

use App\Traits\APIResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class PostStoreRequest extends FormRequest
{
    use APIResponse;

    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'scheduledTime' => 'required|date_format:Y-m-d H:i:s', // Must match format '2025-03-12 01:00:00'
            'mediaUrls' => 'nullable|array',
            'mediaUrls.*' => 'url', // each item as a valid URL
            'listPlatforms' => 'required|array',
            'listPlatforms.*' => 'in:TWITTER,FACEBOOK,REDDIT', // Each platform must be one of these
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();

        return $this->responseErrorValidate($errors, $validator);
    }
}
