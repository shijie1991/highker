<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

use HighKer\Core\Controllers\Admin\AccountController;
use HighKer\Core\Controllers\Admin\AdCategoryController;
use HighKer\Core\Controllers\Admin\AdController;
use HighKer\Core\Controllers\Admin\AjaxController;
use HighKer\Core\Controllers\Admin\ArticleCategoryController;
use HighKer\Core\Controllers\Admin\ArticleController;
use HighKer\Core\Controllers\Admin\ChatConversationController;
use HighKer\Core\Controllers\Admin\ChatMessageController;
use HighKer\Core\Controllers\Admin\CommentController;
use HighKer\Core\Controllers\Admin\CommentImagesController;
use HighKer\Core\Controllers\Admin\FeedAuditController;
use HighKer\Core\Controllers\Admin\FeedController;
use HighKer\Core\Controllers\Admin\FeedImagesController;
use HighKer\Core\Controllers\Admin\FeedTopicController;
use HighKer\Core\Controllers\Admin\HomeController;
use HighKer\Core\Controllers\Admin\NotificationsController;
use HighKer\Core\Controllers\Admin\RegionCityController;
use HighKer\Core\Controllers\Admin\RegionDistrictController;
use HighKer\Core\Controllers\Admin\RegionProvinceController;
use HighKer\Core\Controllers\Admin\SensitiveWordController;
use HighKer\Core\Controllers\Admin\SpiderFeedController;
use HighKer\Core\Controllers\Admin\SpiderSiteController;
use HighKer\Core\Controllers\Admin\SpiderUserController;
use HighKer\Core\Controllers\Admin\TopicGroupController;
use HighKer\Core\Controllers\Admin\UserController;
use HighKer\Core\Controllers\Admin\VipController;
use HighKer\Core\Controllers\Admin\VipOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('admin.home');
Route::get('ajax/users', [AjaxController::class, 'users'])->name('ajax.users');

// 对话相关
Route::resource('conversations', ChatConversationController::class);
Route::resource('messages', ChatMessageController::class);

// 社区相关
Route::resource('topic_group', TopicGroupController::class);
Route::resource('feed', FeedController::class);
Route::resource('feed_images', FeedImagesController::class);
Route::resource('feed_topic', FeedTopicController::class);
Route::resource('feed_audit', FeedAuditController::class);
Route::resource('comments', CommentController::class);
Route::resource('comments_images', CommentImagesController::class);
Route::resource('notifications', NotificationsController::class);

Route::resource('ad', AdController::class);
Route::resource('ad_category', AdCategoryController::class);

Route::resource('sensitive_word', SensitiveWordController::class);
Route::resource('article', ArticleController::class);
Route::resource('article_category', ArticleCategoryController::class);

// 用户相关
Route::resource('account', AccountController::class);
Route::resource('user', UserController::class);

// VIP
Route::resource('vip', VipController::class);
Route::resource('vip_order', VipOrderController::class);
