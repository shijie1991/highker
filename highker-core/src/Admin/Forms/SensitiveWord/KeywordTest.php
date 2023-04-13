<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Admin\Forms\SensitiveWord;

use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

/**
 * @internal
 * @coversNothing
 */
class KeywordTest extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = '测试违禁词';

    /**
     * @param $request
     *
     * @return JsonResponse
     */
    public function handle($request)
    {
        $content = trim(str_replace(["\r\n", "\r", "\n"], '', strip_tags($request['name'])));
        $sensitiveKeywordFilter = app('sensitiveKeywordFilter');
        $result['is_legal'] = $sensitiveKeywordFilter->isLegal($content);
        if ($result['is_legal']) {
            $result['content'] = $content;
        } else {
            $result['filter'] = '';
            $badWords = $sensitiveKeywordFilter->search($content);
            foreach ($badWords as $badWord) {
                $result['filter'] .= '屏蔽词:' . $badWord['word'] . ' ' . '命中次数:' . $badWord['count'] . '<br />';
            }
            $result['content'] = $content;
            $result['replace'] = $sensitiveKeywordFilter->replace($content, '*');
        }

        session()->flash('keyword_test', $result);

        return $this->response()->success('检测成功')->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->textarea('name', '违禁词')->rules('required');
    }

    /**
     * @return Collection|Fluent
     */
    public function data()
    {
        $result = session('keyword_test');

        $content = !empty($result['content']) ? $result['content'] : '';

        return collect(['name' => $content]);
    }
}
