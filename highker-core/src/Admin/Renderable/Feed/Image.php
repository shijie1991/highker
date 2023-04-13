<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Admin\Renderable\Feed;

use Dcat\Admin\Support\LazyRenderable;
use Dcat\Admin\Widgets\Table;
use HighKer\Core\Models\FeedImage;
use Illuminate\Support\Facades\Storage;

class Image extends LazyRenderable
{
    public function render()
    {
        // 获取ID
        /** @noinspection PhpUndefinedFieldInspection */
        $id = $this->key;

        $data = FeedImage::query()->where('feed_id', $id)->get([
            'id',
            'path',
            'created_at',
        ])->makeVisible(['id', 'updated_at', 'created_at'])->toArray();

        $data = collect($data)->map(function ($item) {
            $url = Storage::url($item['path']);
            /* @noinspection HtmlRequiredAltAttribute */
            $item['path'] = "<img src='{$url}' style='max-width:100px;max-height:100px;cursor:pointer' class='preview_image img img-thumbnail'>";

            return $item;
        })->toArray();

        // <i class="fa fa-circle" style="font-size: 13px;color: #dda451"></i>&nbsp;&nbsp;待审核

        $headers = [
            'ID',
            '图片',
            '创建时间',
        ];

        return Table::make($headers, $data);
    }
}
