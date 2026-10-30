<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Quy tắc giống model LIENHE của đồ án cũ
    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:100'],
            'email'   => ['required', 'email', 'max:100'],
            'phone'   => ['required', 'regex:/^0\d{9}$/'],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Vui lòng nhập họ và tên',
            'name.max'         => 'Họ tên không được vượt quá 100 ký tự',
            'email.required'   => 'Vui lòng nhập email',
            'email.email'      => 'Email không hợp lệ',
            'email.max'        => 'Email không được vượt quá 100 ký tự',
            'phone.required'   => 'Vui lòng nhập số điện thoại',
            'phone.regex'      => 'Số điện thoại phải có 10 số và bắt đầu bằng số 0',
            'message.required' => 'Vui lòng nhập nội dung',
            'message.max'      => 'Nội dung không được vượt quá 2000 ký tự',
        ];
    }
}
