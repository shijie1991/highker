<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models;

use HighKer\Core\Enum\CommentLevel;
use HighKer\Core\Enum\CommentStatus;
use HighKer\Core\Models\Traits\Likeable;
use HighKer\Core\Observers\CommentObserver;
use HighKer\Core\Support\HighKer;
use HighKer\Core\Utils\ImageUtils;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Throwable;

/**
 * Class FeedComment.
 *
 * @property int            id
 * @property int            user_id
 * @property int            feed_id
 * @property int            status
 * @property int            parent_id
 * @property int            reply_id
 * @property int            level
 * @property int            like_count
 * @property int            reply_count
 * @property CommentContent content
 * @property string         deleted_at
 * @property string         created_at
 * @property string         updated_at
 * @property string         format_content
 * @property User           user
 * @property Feed           feed
 * @property CommentImage   images
 * @property Comment        parent
 * @property Comment        reply_parent
 * @property Comment        replys
 * @property Comment        second_replys
 */
class Comment extends BaseModel
{
    use Likeable;

    protected $fillable = [
        'user_id',
        'feed_id',
        'status',
        'parent_id',
        'reply_id',
        'level',
        'content',
    ];

    protected $attributes = [
        'status'      => 0,
        'like_count'  => 0,
        'reply_count' => 0,
    ];

    protected $hidden = [
        'status',
        'deleted_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('approve', function (Builder $builder) {
            $builder->where('status', CommentStatus::APPROVE);
        });

        static::observe(CommentObserver::class);
    }

    public function formatContent(): Attribute
    {
        return new Attribute(
            get: fn ($value, $attributes) => Str::limit(str_replace(PHP_EOL, '', $this->content->text), 50)
        );
    }

    /**
     * @param null $text
     * @param null $images
     *
     * @throws Throwable
     */
    public static function createComment(Feed $feed, User|Authenticatable $user, $text = null, $images = null)
    {
        return Highker::db()->transaction(function () use ($feed, $user, $text, $images) {
            $comment = Comment::query()->create([
                'feed_id' => $feed->id,
                'user_id' => $user->id,
            ]);

            if ($text) {
                $comment->content()->create(['text' => $text]);
            }

            if ($images) {
                // 获取 图片 尺寸
                [$width, $height] = ImageUtils::getSize($images);
                if ($path = $images->store(Highker::uploadDir('comment'))) {
                    $comment->images()->create([
                        'user_id' => $comment->user_id,
                        'width'   => $width,
                        'height'  => $height,
                        'path'    => $path,
                    ]);
                }
            }

            return $comment->makeHidden(['feed'])->loadMissing(['content', 'images']);
        });
    }

    /**
     * @param null $text
     * @param null $images
     *
     * @throws Throwable
     */
    public static function createReply(Comment $comment, User|Authenticatable $user, $text = null, $images = null)
    {
        return Highker::db()->transaction(function () use ($comment, $user, $text, $images) {
            // 根据等级设置是否是一级回复 否则是 二级回复
            $relation = $comment->level == CommentLevel::COMMENT ? $comment->replys() : $comment->second_replys();

            $reply = $relation->create([
                'feed_id'   => $comment->feed_id,
                'user_id'   => $user->id,
                'parent_id' => $comment->level !== CommentLevel::COMMENT ? $comment->parent_id : null,
            ]);

            $reply->load(['reply_parent' => function (BelongsTo $query) {
                $query->with(['content', 'images', 'user']);
            }]);

            if ($text) {
                $reply->content()->create(['text' => $text]);
            }

            if ($images) {
                // 获取 图片 尺寸
                [$width, $height] = ImageUtils::getSize($images);
                if ($path = $images->store(Highker::uploadDir('comment'))) {
                    $comment->images()->create([
                        'width'  => $width,
                        'height' => $height,
                        'path'   => $path,
                    ]);
                }
            }

            return $reply->makeHidden(['feed'])->loadMissing(['content', 'images']);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function content(): HasOne
    {
        return $this->hasOne(CommentContent::class);
    }

    public function images(): HasOne
    {
        return $this->hasOne(CommentImage::class);
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    /**
     * 一级回复的父级评论.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id', 'id');
    }

    /**
     * 二级回复的父级评论.
     */
    public function reply_parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'reply_id', 'id');
    }

    /**
     * 一级回复.
     */
    public function replys(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id', 'id')->orderByDesc('id');
    }

    /**
     * 二级回复.
     */
    public function second_replys(): HasMany
    {
        return $this->hasMany(Comment::class, 'reply_id', 'id');
    }
}
