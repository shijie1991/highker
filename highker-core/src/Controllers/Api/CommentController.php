<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Api;

use HighKer\Core\Models\Comment;
use HighKer\Core\Models\Feed;
use HighKer\Core\Requests\CommentCreateRequest;
use HighKer\Core\Requests\CommentReplyRequest;
use HighKer\Core\Resources\CommentResource;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Throwable;

class CommentController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['forbidden_user'])->except(['index', 'show', 'replyList']);
        $this->middleware(['locked_user'])->only(['store', 'reply']);
    }

    public function index(Feed $feed)
    {
        $comments = $feed->comments()->with(['user', 'content', 'images', 'replys' => function (HasMany $query) {
            $query->with(['content', 'user']);
        }])->orderByDesc('id')->simplePaginate();

        // replys 只取 2 条
        $comments->through(function (Comment $comment) {
            $comment->setRelation('replys', $comment->replys->take(2));

            return $comment;
        });

        if (Auth::check()) {
            // 是否点赞
            Auth::user()->attachLikeStatus($comments);
        }

        return $this->success(CommentResource::collection($comments));
    }

    public function show(Comment $comment)
    {
        $comment->loadMissing(['user', 'content', 'images']);

        if (Auth::check()) {
            // 是否点赞
            Auth::user()->attachLikeStatus($comment);
        }

        return $this->success(CommentResource::make($comment));
    }

    /**
     * 创建评论.
     *
     * @throws Throwable
     *
     * @return JsonResource|JsonResponse
     */
    public function store(CommentCreateRequest $request, Feed $feed)
    {
        $comment = Comment::createComment(
            $feed,
            Auth::user(),
            $request->input('content'),
            $request->file('images')
        );

        return $this->success(CommentResource::make($comment), '评论成功');
    }

    /**
     * 创建回复.
     *
     * @throws Throwable
     *
     * @return JsonResource|JsonResponse
     */
    public function reply(CommentReplyRequest $request, Comment $comment)
    {
        $reply = Comment::createReply(
            $comment,
            Auth::user(),
            $request->input('content'),
        );

        return $this->success(CommentResource::make($reply), '回复成功');
    }

    /**
     * @return JsonResource|JsonResponse
     */
    public function replyList(Comment $comment)
    {
        $comments = $comment->replys()
            ->with(['user', 'content', 'images', 'reply_parent' => function (BelongsTo $query) {
                $query->with(['content', 'images', 'user']);
            }])
            ->orderByDesc('id')
            ->simplePaginate()
        ;

        if (Auth::check()) {
            // 是否点赞
            Auth::user()->attachLikeStatus($comments);
        }

        return $this->success(CommentResource::collection($comments));
    }

    public function like(Comment $comment)
    {
        Auth::user()->like($comment);

        return $this->success(null, '点赞成功');
    }

    public function unlike(Comment $comment)
    {
        Auth::user()->unlike($comment);

        return $this->success(null, '取消点赞成功');
    }
}
