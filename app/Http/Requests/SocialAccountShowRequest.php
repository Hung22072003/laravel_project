<?php

namespace App\Http\Requests;

use App\Traits\APIResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class SocialAccountShowRequest extends FormRequest
{
    use APIResponse;

    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'platform' => 'in:TWITTER,FACEBOOK,REDDIT', 
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();

        return $this->responseErrorValidate($errors, $validator);
    }
}
