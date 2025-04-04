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
            'scheduledTime' => 'date_format:Y-m-d H:i:s', // Must match format '2025-03-12 01:00:00'
            'mediaUrls' => 'nullable|array',
            'mediaUrls.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'listPlatforms' => 'required|array',
            'listPlatforms.*' => 'in:TWITTER,FACEBOOK,REDDIT,LINKEDIN', // Each platform must be one of these
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();

        return $this->responseErrorValidate($errors, $validator);
    }
}
