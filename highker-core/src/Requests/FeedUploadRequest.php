<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Requests;

class FeedUploadRequest extends Request
{
    public function rules(): array
    {
        return [
            'image' => ['required', 'mimes:jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => '请选择上传的图片',
            'image.mimes'    => '图片仅支持 JPG,PNG 格式',
            'image.max'      => '图片大小不能超过 5M',
        ];
    }
}
