<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Requests;

class CommentReplyRequest extends Request
{
    public function rules()
    {
        return [
            'content' => ['required', 'max:500'],
        ];
    }

    public function messages()
    {
        return [
            'content.required' => '请输入回复内容论',
            'content.max'      => '内容太多啦! 最多 500 个字',
        ];
    }
}
