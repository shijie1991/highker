<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Factories;

use HighKer\Core\Models\Comment;
use HighKer\Core\Models\Feed;
use HighKer\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        // 随机取一个用户
        $user = User::query()->inRandomOrder()->first();

        // 随机取一个动态
        $feed = Feed::query()->inRandomOrder()->withoutGlobalScope('approve')->first();

        return [
            'user_id' => $user->id,
            'feed_id' => $feed->id,
        ];
    }

    public function configure(): CommentFactory
    {
        return $this->afterCreating(function (Comment $comment) {
            $comment->content()->create(['text' => $this->faker->text($this->faker->numberBetween(5, 500))]);
        });
    }
}
