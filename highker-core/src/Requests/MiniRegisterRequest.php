<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Requests;

use HighKer\Core\Enum\UserGender;
use Illuminate\Validation\Rule;

class MiniRegisterRequest extends Request
{
    public function rules()
    {
        return [
            'code'       => ['required'],
            'phone'      => ['required'],
            'name'       => ['required', 'max:18'],
            'gender'     => ['required', Rule::in([UserGender::MALE, UserGender::FEMALE])],
            'avatar'     => ['required_without:avatar_url', 'mimes:jpeg,png', 'max:5120'],
            'avatar_url' => ['required_without:avatar'],
        ];
    }

    public function messages()
    {
        return [
            'code.required'           => '参数错误',
            'phone.required'          => '参数错误',
            'name.required'           => '请输入昵称',
            'name.max'                => '昵称最多 18 个字',
            'gender.required'         => '请选择性别',
            'gender.in'               => '请选择性别',
            'avatar.required_without' => '请上传头像',
            'avatar.mimes'            => '图片仅支持 JPG,PNG 格式',
            'avatar.max'              => '图片大小不能超过 5M',
            'avatar_url.avatar_url'   => '请上传头像',
        ];
    }
}
