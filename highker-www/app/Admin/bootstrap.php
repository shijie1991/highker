<?php

use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Form;
use Dcat\Admin\Grid\Column;
use Dcat\Admin\Grid\Filter;
use Dcat\Admin\Show;
use HighKer\Core\Admin\Extensions\UserName;

/**
 * Dcat-admin - admin builder based on Laravel.
 * @author jqh <https://github.com/jqhph>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 *
 * extend custom field:
 * Dcat\Admin\Form::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Column::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Filter::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */
Form::resolving(function (Form $form) {
    // 去掉`继续编辑`checkbox
    $form->disableEditingCheck();
    // 去掉`继续创建`checkbox
    $form->disableCreatingCheck();
    // 去掉`查看`checkbox
    $form->disableViewCheck();
    $form->tools(function (Form\Tools $tools) {
        $tools->disableDelete();
        $tools->disableView();
//        $tools->disableList();
    });
});

Grid::resolving(function (Grid $grid) {
    $grid->disableRowSelector();
});

Column::extend('userName', userName::class);
