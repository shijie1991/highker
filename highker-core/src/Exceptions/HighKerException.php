<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Exceptions;

use Exception;
use HighKer\Core\Enum\ResponseCode;

class HighKerException extends Exception
{
    public function __construct($message, int $code = ResponseCode::SYSTEM_ERROR)
    {
        parent::__construct($message, $code);
    }
}
