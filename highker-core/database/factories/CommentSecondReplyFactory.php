<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Factories;

use HighKer\Core\Enum\CommentLevel;
use HighKer\Core\Models\Comment;
use HighKer\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentSecondReplyFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        // 随机取一个用户
        $user = User::query()->inRandomOrder()->first();

        // 随机取一个评论
        $comment = Comment::query()->inRandomOrder()->first();

        return [
            'user_id' => $user->id,
            'feed_id' => $comment->feed_id,
            'parent_id' => $comment->parent_id,
            'reply_id' => $comment->level !== CommentLevel::COMMENT ? $comment->id : null,
        ];
    }

    public function configure(): CommentSecondReplyFactory
    {
        return $this->afterCreating(function (Comment $comment) {
            $comment->content()->create(['text' => $this->faker->text($this->faker->numberBetween(5, 500))]);
        });
    }
}
