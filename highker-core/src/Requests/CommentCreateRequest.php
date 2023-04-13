<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Requests;

class CommentCreateRequest extends Request
{
    public function rules(): array
    {
        return [
            'content' => ['required_without:images', 'max:500'],
            'images'  => ['mimes:jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required_without' => '请输入评论内容或图片评论',
            'content.max'              => '内容太多啦! 最多 500 个字',
            'images.mimes'             => '图片仅支持 JPG,PNG 格式',
            'images.max'               => '图片大小不能超过 5M',
        ];
    }
}
