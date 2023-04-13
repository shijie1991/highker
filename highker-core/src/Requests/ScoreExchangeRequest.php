<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Requests;

use HighKer\Core\Enum\ScoreExchange;
use Illuminate\Validation\Rule;

class ScoreExchangeRequest extends Request
{
    public function rules()
    {
        return [
            'privilege' => ['required', Rule::in(ScoreExchange::LIST)],
        ];
    }

    public function messages()
    {
        return [
            'privilege.required' => '请选择兑换的特权',
            'privilege.in'       => '请选择兑换的特权',
        ];
    }
}
