<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Class StringArray.
 *
 * 字符串拆分维数组并验证 例:1,2,3
 */
class StringArray implements Rule
{
    protected string $singleRule = '';

    protected array $rule = ['string', 'int'];

    protected int $maxLen = 3;

    protected string $message = 'The :attribute must be StringArray.';

    public function __construct($singleRule, $maxLen = null, $message = null)
    {
        $this->singleRule = collect($this->rule)->contains($singleRule) ? $singleRule : 'int';

        $this->maxLen = $maxLen ?? $this->maxLen;

        $this->message = $message ?? $this->message;
    }

    public function passes($attribute, $value)
    {
        $values = explode(',', $value);

        if ($this->maxLen && count($values) > $this->maxLen) {
            return false;
        }

        foreach ($values as $val) {
            if ($this->singleRule === 'string') {
                if (!is_string($val)) {
                    return false;
                }
            }
            if ($this->singleRule === 'int') {
                if (!is_numeric($val)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function message()
    {
        return $this->message;
    }
}
