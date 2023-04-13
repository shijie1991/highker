<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Admin\Renderable\Comment;

use Dcat\Admin\Support\LazyRenderable;
use Dcat\Admin\Widgets\Table;
use HighKer\Core\Models\CommentImage;
use Illuminate\Support\Facades\Storage;

class Image extends LazyRenderable
{
    /** @noinspection PhpUndefinedFieldInspection */
    public function render()
    {
        // 获取ID
        $id = $this->key;

        $data = CommentImage::query()->where('comment_id', $id)->get([
            'id',
            'path',
            'created_at',
        ])->toArray();

        $data = collect($data)->map(function ($item) {
            $url = Storage::url($item['path']);
            /* @noinspection HtmlRequiredAltAttribute */
            $item['path'] = "<img src='{$url}' style='max-width:100px;max-height:100px;cursor:pointer' class='preview_image img img-thumbnail'>";

            return $item;
        })->toArray();

        $headers = [
            'ID',
            '图片',
            '创建时间',
        ];

        return Table::make($headers, $data);
    }
}
