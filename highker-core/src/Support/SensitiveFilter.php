<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Support;

use AbelZhou\Tree\TrieTree;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\SensitiveWords;
use Illuminate\Support\Facades\Cache;

/**
 * Class SensitiveFilter.
 */
class SensitiveFilter
{
    protected ?TrieTree $trieTree = null;

    protected ?string $defaultReplace = null;

    /**
     * SensitiveFilter constructor.
     *
     * @throws HighKerException
     */
    public function __construct()
    {
        $this->defaultReplace = '[嗨刻美好生活]';
        $this->buildTreeFromDataBase();
    }

    /**
     * 构建铭感词树 For 数据库.
     *
     * @throws HighKerException
     *
     * @return $this
     */
    public function buildTreeFromDataBase(): SensitiveFilter
    {
        [$key, $expire] = Highker::getCacheKey('other:sensitive-words', 'all');

        $keyWordList = Cache::remember($key, $expire, function () {
            return SensitiveWords::query()->where('status', 1)->pluck('name');
        });

        $this->trieTree = new TrieTree();
        foreach ($keyWordList as $keyword) {
            $this->trieTree->append($keyword);
        }

        return $this;
    }

    /**
     * 获取 敏感词树.
     */
    public function getTree(): array
    {
        return $this->trieTree->getTree();
    }

    /**
     * 检测文字中的敏感词.
     *
     * @return array|bool
     */
    public function search(string $content)
    {
        return $this->trieTree->search($content);
    }

    /**
     * 被检测内容是否合法.
     */
    public function isLegal(string $content): bool
    {
        return !$this->search($content);
    }

    /**
     * 替换敏感字字符.
     */
    public function replace(string $content, string $replace = ''): string
    {
        $badWords = $this->search($content);
        if ($badWords) {
            foreach ($badWords as $badWord) {
                $replaceTmp = $replace ? str_repeat($replace, mb_strlen($badWord['word'])) : $this->defaultReplace;
                $content = str_replace($badWord['word'], $replaceTmp, $content);
            }
        }

        return $content;
    }
}
