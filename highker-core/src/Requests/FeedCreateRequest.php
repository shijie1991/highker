<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Requests;

use HighKer\Core\Rules\StringArray;

class FeedCreateRequest extends Request
{
    protected function prepareForValidation()
    {
        $this->merge([
            'images' => json_decode($this->images, true),
        ]);
    }

    public function rules(): array
    {
        return [
            'content'         => ['required_without:images', 'max:1000'],
            'images'          => ['max:9'],
            'images.*.path'   => ['required'],
            'images.*.width'  => ['required'],
            'images.*.height' => ['required'],
            'topic_id'        => ['nullable', new StringArray('int', 3, '请选择正确的话题')],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required_without' => '请输入内容或上传图片',
            'content.max'              => '内容太多啦! 最多 1000 个字',
            'images.max'               => '最多上传 9 张图片',
            'images.*.path.required'   => '图片参数错误',
            'images.*.width.required'  => '图片参数错误',
            'images.*.height.required' => '图片参数错误',
        ];
    }
}
