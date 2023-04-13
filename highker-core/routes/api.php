<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

use HighKer\Core\Controllers\Api\AuthController;
use HighKer\Core\Controllers\Api\ChatBoxController;
use HighKer\Core\Controllers\Api\ChatConversationController;
use HighKer\Core\Controllers\Api\ChatMessageController;
use HighKer\Core\Controllers\Api\CommentController;
use HighKer\Core\Controllers\Api\FeedController;
use HighKer\Core\Controllers\Api\IndexController;
use HighKer\Core\Controllers\Api\NotificationController;
use HighKer\Core\Controllers\Api\TopicController;
use HighKer\Core\Controllers\Api\UserController;
use HighKer\Core\Controllers\Api\VipController;
use HighKer\Core\Controllers\Api\WechatController;
use Illuminate\Support\Facades\Route;

Route::any('o6BdZNrT0g.txt', function () {
    return '72dedfb3dbd7d51c581ef11d5532e73e';
})->withoutMiddleware(['auth:sanctum']);

Route::any('wechat', [WeChatController::class, 'serve'])->withoutMiddleware(['auth:sanctum']);
Route::any('wechat/notify', [WeChatController::class, 'notify'])->withoutMiddleware(['auth:sanctum']);

// 初始化
Route::get('initialization', [IndexController::class, 'index'])->withoutMiddleware(['auth:sanctum']);
Route::get('test', [IndexController::class, 'getSocket'])->withoutMiddleware(['auth:sanctum']);

Route::get('agreement', [IndexController::class, 'agreement'])->withoutMiddleware(['auth:sanctum']);
Route::get('faq', [IndexController::class, 'faq'])->withoutMiddleware(['auth:sanctum']);

// 排行榜
Route::get('ranking', [IndexController::class, 'ranking'])->withoutMiddleware(['auth:sanctum']);
Route::get('ranking/{slug}', [IndexController::class, 'rankingInfo'])->withoutMiddleware(['auth:sanctum']);

// 盲盒
Route::get('box', [ChatBoxController::class, 'index']);
Route::post('box', [ChatBoxController::class, 'store']);
Route::get('box/count', [ChatBoxController::class, 'count']);

// 聊天对话相关
Route::get('conversations', [ChatConversationController::class, 'index']);
Route::post('conversations/{user}', [ChatConversationController::class, 'store'])->whereNumber('user');
Route::get('conversations/{user}/exist', [ChatConversationController::class, 'exist'])->whereNumber('user');
Route::get('conversations/{conversation}/destroy', [ChatConversationController::class, 'destroy'])->whereNumber('conversation');
Route::get('conversations/red', [ChatConversationController::class, 'red']);

// 聊天信息相关
Route::get('conversations/{conversation}/message', [ChatMessageController::class, 'index']);
Route::post('conversations/{conversation}/message', [ChatMessageController::class, 'store']);
Route::get('conversations/{conversation}/message/read', [ChatMessageController::class, 'read']);

// 账号相关
Route::post('auth/login', [AuthController::class, 'login'])->withoutMiddleware(['auth:sanctum']);
Route::post('auth/mini/login', [AuthController::class, 'miniLogin'])->withoutMiddleware(['auth:sanctum']);
Route::post('auth/mini/register', [AuthController::class, 'miniRegister'])->withoutMiddleware(['auth:sanctum']);
Route::get('auth/me', [AuthController::class, 'me']);
Route::get('auth/logout', [AuthController::class, 'logout']);
Route::get('auth/destroy', [AuthController::class, 'destroy']);

// 话题相关
Route::get('topics', [TopicController::class, 'index'])->withoutMiddleware(['auth:sanctum']);
// whereNumber 处理 路由冲突的问题
Route::get('topics/{topic}', [TopicController::class, 'show'])->whereNumber('topic')->withoutMiddleware(['auth:sanctum']);
Route::get('topics/{topic}/feeds', [TopicController::class, 'feeds'])->withoutMiddleware(['auth:sanctum']);
Route::post('topics/{topic}/subscribe', [TopicController::class, 'subscribe']);
Route::get('topics/{topic}/unsubscribe', [TopicController::class, 'unsubscribe']);

// 动态相关
Route::get('feeds', [FeedController::class, 'index'])->withoutMiddleware(['auth:sanctum']);
Route::post('feeds', [FeedController::class, 'store']);
Route::post('feeds/upload', [FeedController::class, 'upload']);
Route::get('feeds/{feed}', [FeedController::class, 'show'])->withTrashed()->withoutMiddleware(['auth:sanctum']);
Route::get('feeds/{feed}/destroy', [FeedController::class, 'destroy']);
Route::post('feeds/{feed}/report', [FeedController::class, 'report']);

// 动态点赞
Route::get('feeds/{feed}/likes', [FeedController::class, 'likeList']);
Route::post('feeds/{feed}/likes', [FeedController::class, 'like']);
Route::get('feeds/{feed}/unlikes', [FeedController::class, 'unlike']);

// 动态评论相关
Route::get('feeds/{feed}/comments', [CommentController::class, 'index'])->withoutMiddleware(['auth:sanctum']);
Route::post('feeds/{feed}/comments', [CommentController::class, 'store']);
Route::get('comments/{comment}', [CommentController::class, 'show'])->withoutMiddleware(['auth:sanctum']);

// 动态评论回复
Route::post('comments/{comment}/replys', [CommentController::class, 'reply']);
Route::get('comments/{comment}/replys', [CommentController::class, 'replyList'])->withoutMiddleware(['auth:sanctum']);

// 动态评论点赞
Route::post('comments/{comment}/likes', [CommentController::class, 'like']);
Route::get('comments/{comment}/unlikes', [CommentController::class, 'unlike']);

// 通知
Route::get('notifications/system', [NotificationController::class, 'system']);
Route::get('notifications/interactive', [NotificationController::class, 'interactive']);

// 用户关注
Route::get('users/{user}', [UserController::class, 'index'])->whereNumber('user')->withoutMiddleware(['auth:sanctum']);
Route::get('users/{user}/feeds', [UserController::class, 'feeds'])->withoutMiddleware(['auth:sanctum']);
Route::get('users/{user}/following', [UserController::class, 'following']);
Route::get('users/{user}/followers', [UserController::class, 'followers']);
Route::post('users/{user}/follow', [UserController::class, 'follow']);
Route::get('users/{user}/unfollow', [UserController::class, 'unfollow']);

// 我的
Route::post('users/setting', [UserController::class, 'setting']);
Route::get('users/task', [UserController::class, 'task']);
Route::get('users/task/log', [UserController::class, 'taskLog']);
Route::get('users/level', [UserController::class, 'level']);
Route::get('users/visits', [UserController::class, 'visits']);
Route::get('users/score', [UserController::class, 'score']);
Route::get('users/score/log', [UserController::class, 'scoreLog']);
Route::post('users/score/exchange', [UserController::class, 'scoreExchange']);

// VIP
Route::get('vip', [VipController::class, 'vip']);
Route::post('vip/pay', [VipController::class, 'pay']);
