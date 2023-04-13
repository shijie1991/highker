<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Requests;

class LoginRequest extends Request
{
    public function rules()
    {
        return [
            'name'     => 'required',
            'password' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required'     => '请输入用户名',
            'password.required' => '请输入密码',
        ];
    }
}
