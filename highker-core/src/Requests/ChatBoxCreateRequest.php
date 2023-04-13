<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Requests;

class ChatBoxCreateRequest extends Request
{
    public function rules(): array
    {
        return [
            'content'  => ['required_without_all:image,voice', 'max:500'],
            'image'    => ['required_without_all:content,voice', 'mimes:jpeg,png', 'max:5120'],
            'voice'    => ['required_without_all:content,image'],
            'duration' => ['required_with:voice'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required_without_all' => '请输入盲盒内容',
            'content.max'                  => '最多 500 个字',
            'image.required_without_all'   => '请选择盲盒图片',
            'image.mimes'                  => '图片仅支持 JPG,PNG 格式',
            'image.max'                    => '图片大小不能超过 5M',
            'voice.required_without_all'   => '请发送盲盒语音',
            'voice.mimetypes'              => '语音仅支持 MP3 格式',
            'duration.required_with'       => '录音总时长丢失',
        ];
    }
}
