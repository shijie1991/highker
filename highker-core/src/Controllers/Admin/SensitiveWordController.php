<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Admin;

use Dcat\Admin\Grid;
use Dcat\Admin\Grid\Filter;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Box;
use Dcat\Admin\Widgets\Callout;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Widgets\Tab;
use HighKer\Core\Admin\Forms\SensitiveWord\KeywordTest;
use HighKer\Core\Models\SensitiveWords;

class SensitiveWordController extends AdminController
{
    protected $title = '违禁词';
    protected $description = '管理';

    /**
     * @return Content
     */
    public function index(Content $content)
    {
        $content->header($this->title);
        $content->description($this->description);
        $content->row(function (Row $row) {
            $row->column(7, function (Column $column) {
                $grid = new Grid(new SensitiveWords());
                // 禁用创建按钮
                $grid->disableCreateButton();
                // 禁用行操作列
                $grid->disableActions();
                $grid->disableRowSelector();
                $grid->filter(function (Filter $filter) {
                    $filter->like('name', '关键词');
                });

                $grid->model()->orderBy('id', 'desc');
                $grid->column('name', '关键字')->editable();
                $grid->column('status', '是否启用')->switch();
                $grid->column('created_at', '创建时间');
                $column->append($grid);
            });
            $row->column(5, function (Column $column) {
                $form = new Form(new SensitiveWords());
                $form->text('name', '名称')->rules('required');
                $form->switch('status', '是否启用')->default(1);
                $column->append((new Box('添加违禁词', $form)));

                $column->append((new Box('测试违禁词', new KeywordTest())));

                if ($result = session('keyword_test')) {
                    $tab = new Tab();
                    if ($result['is_legal']) {
                        $tab->add('未命中屏蔽词', (new Callout($result['content']))->style('success'));
                    } else {
                        $tab->add('命中屏蔽词', (new Callout($result['filter']))->style('danger'));
                        $tab->add('原文', (new Callout($result['content']))->style('warning'));
                        $tab->add('替换后', (new Callout($result['replace']))->style('success'));
                    }
                    $column->append((new Box('测试结果', $tab)));
                }
            });
        });

        return $content;
    }

    /**
     * @return \Dcat\Admin\Form
     */
    public function form()
    {
        $form = new \Dcat\Admin\Form(new SensitiveWords());
        $form->display('id', 'ID');
        $form->text('name', '名称')->rules('required');
        $form->switch('status', '是否启用');

        return $form;
    }
}
