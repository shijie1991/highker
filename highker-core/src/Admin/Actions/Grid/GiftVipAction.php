<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Admin\Actions\Grid;

use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Widgets\Modal;
use HighKer\Core\Admin\Forms\GiftVip;

class GiftVipAction extends RowAction
{
    /**
     * @return string
     */
    protected $title = "<i class='fa fa-gift'></i> 赠送 VIP";

    public function render()
    {
        // 实例化表单类并传递自定义参数
        $form = GiftVip::make()->payload(['user_id' => $this->getKey()]);

        return Modal::make()
            ->lg()
            ->title($this->title)
            ->body($form)
            ->button($this->title)
        ;
    }
}
