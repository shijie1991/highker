<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Admin\Extensions;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid\Displayers\AbstractDisplayer;
use HighKer\Core\Enum\UserGender;

class UserName extends AbstractDisplayer
{
    public function display(): string
    {
        $user = $this->row->user;
        $name = $user->name ?? '全部用户';
        $gender = $user->gender ?? '';

        $color = match ($gender) {
            UserGender::MALE   => Admin::color()->blue(),
            UserGender::FEMALE => Admin::color()->pink(),
            default            => Admin::color()->orange2(),
        };
        $url = isset($user->id) ? admin_url('user?id='.$user->id) : '';

        if ($url) {
            $html = "<i class='fa fa-circle' style='font-size: 13px;color:{$color}'></i><a href='{$url}' target='_blank'>  {$name}</a>";
        } else {
            $html = "<i class='fa fa-circle' style='font-size: 13px;color:{$color}'></i> {$name}";
        }

        return <<<EOT
                        {$html}
            EOT;
    }
}
