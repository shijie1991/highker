<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Admin\Extensions\Tools;

use Dcat\Admin\Grid\Tools\AbstractTool;

class CommentImages extends AbstractTool
{
    public function render()
    {
        $request = request();

        if ($request->path() == 'comments_images') {
            $url = url('comments');
            $title = '列表模式';
            $icon = 'icon-list';
        } else {
            $url = url('comments_images');
            $title = '图片模式';
            $icon = 'icon-image';
        }

        return <<<HTML
                <div class="btn btn-primary grid-refresh btn-mini btn-outline" style="margin-right:3px">
                    <a href="{$url}">
                      <i class="feather {$icon}"></i><span class="d-none d-sm-inline">&nbsp; {$title}</span>
                    </a>
                </div>
            HTML;
    }
}
