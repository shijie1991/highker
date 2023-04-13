<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Enum;

class ScoreExchange extends BaseEnum
{
    public const ADD_BOX = 1;
    public const GET_BOX = 2;
    public const PRIVATE_MESSAGE = 3;

    public const LIST = [
        self::ADD_BOX,
        self::GET_BOX,
        self::PRIVATE_MESSAGE,
    ];

    public const MAP = [
        self::ADD_BOX => [
            'slug'  => self::ADD_BOX,
            'name'  => '放盲盒 1 次',
            'group' => 1,
            'score' => 10,
            'count' => 1,
        ],
        self::GET_BOX => [
            'slug'  => self::GET_BOX,
            'name'  => '拆盲盒 1 次',
            'group' => 2,
            'score' => 10,
            'count' => 1,
        ],
        self::PRIVATE_MESSAGE => [
            'slug'  => self::PRIVATE_MESSAGE,
            'name'  => '私信 1 次',
            'group' => 3,
            'score' => 10,
            'count' => 1,
        ],
    ];
}
