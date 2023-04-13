<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Requests;

use HighKer\Core\Enum\ReportReason;
use Illuminate\Validation\Rule;

class ReportRequest extends Request
{
    public function rules()
    {
        return [
            'reason' => ['required', Rule::in(ReportReason::LIST)],
        ];
    }

    public function messages()
    {
        return [
            'reason.required' => '请选择举报理由',
            'reason.in'       => '请选择举报理由',
        ];
    }
}
