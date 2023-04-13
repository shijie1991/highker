<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Requests;

use HighKer\Core\Enum\UserEmotion;
use HighKer\Core\Enum\UserGender;
use HighKer\Core\Enum\UserPurpose;
use HighKer\Core\Rules\EditNameThrottle;
use Illuminate\Validation\Rule;

class UserSettingRequest extends Request
{
    public function rules()
    {
        return [
            'name' => [
                'sometimes',
                'required',
                new EditNameThrottle(),
                'max:18',
            ],
            'gender' => [
                'sometimes',
                'required',
                Rule::in([UserGender::MALE, UserGender::FEMALE]),
            ],
            'emotion' => [
                'sometimes',
                'required',
                Rule::in(UserEmotion::LIST),
            ],
            'purpose' => [
                'sometimes',
                'required',
                Rule::in(UserPurpose::LIST),
            ],
            'birthday' => [
                'sometimes',
                'required',
                'date_format:Y-m-d',
            ],
            'description' => [
                'sometimes',
                'nullable',
                'max:100',
            ],
            'region' => [
                'sometimes',
                'required',
                'max:100',
            ],
            'avatar' => [
                'sometimes',
                'required',
                'mimes:jpeg,png',
                'max:5120',
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required'        => '请输入昵称',
            'name.max'             => '昵称最多输入 18 个字',
            'gender.required'      => '请选择性别',
            'gender.in'            => '请选择性别',
            'emotion.required'     => '请选择情感状态',
            'emotion.in'           => '请选择情感状态',
            'purpose.required'     => '请选择目的',
            'purpose.in'           => '请选择目的态',
            'birthday.required'    => '请选择出生日期',
            'birthday.date_format' => '请选择出生日期',
            'description.required' => '请输入个性签名',
            'description.max'      => '个性签名最多输入 100 个字',
            'avatar.required'      => '请选择上传的头像',
            'avatar.mimes'         => '头像仅支持 JPG,PNG 格式',
            'avatar.max'           => '头像大小不能超过 5M',
        ];
    }
}
