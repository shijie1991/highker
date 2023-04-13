<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Requests;

use HighKer\Core\Enum\VipProduct;
use Illuminate\Validation\Rule;

class VipRequest extends Request
{
    public function rules()
    {
        return [
            'slug' => ['required', Rule::in(VipProduct::LIST)],
        ];
    }

    public function messages()
    {
        return [
            'slug.required' => '参数错误',
            'slug.in'       => '参数错误',
        ];
    }
}
