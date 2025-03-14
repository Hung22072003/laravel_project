<?php

namespace App\Http\Requests;

use App\Traits\APIResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class PostUpdateRequest extends FormRequest
{
    use APIResponse;

    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'scheduledTime' => 'required|date_format:Y-m-d H:i:s', 
            'mediaUrls' => 'nullable|array',
            'mediaUrls.*' => 'url',
            'listPlatforms' => 'required|array',
            'listPlatforms.*' => 'in:TWITTER,FACEBOOK,REDDIT', 
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();

        return $this->responseErrorValidate($errors, $validator);
    }
}
