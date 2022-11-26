<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DangNhapRequets extends FormRequest
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
            'password' => 'required',
            'email' => 'required|email',
            
        ];
    }
    public function messages()
    {
        return [
                'password.required' => 'Vui lòng nhập mật khẩu',
                'email.required' => 'Vui lòng nhập email',
                'email.email' => 'Vui lòng nhập đúng định dạng abc@gmail.com'
            ];
    }
}
