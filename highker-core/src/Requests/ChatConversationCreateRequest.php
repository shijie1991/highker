<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Requests;

class ChatConversationCreateRequest extends Request
{
    public function rules(): array
    {
        return [
            'content' => ['required', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => '请输入内容或上传图片',
            'content.max'      => '内容太多啦!',
        ];
    }
}
