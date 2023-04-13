<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

use HighKer\Core\Enum\ChatMessageType;
use HighKer\Core\Enum\FeedImageStatus;
use HighKer\Core\Enum\FeedStatus;
use HighKer\Core\Enum\ScoreLogType;
use HighKer\Core\Enum\UserGender;
use HighKer\Core\Enum\UserReviewType;
use HighKer\Core\Enum\UserStatus;
use HighKer\Core\Enum\VipOrderType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateCoreTable.
 */
class CreateCoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @noinspection SqlDialectInspection
     * @noinspection SqlNoDataSourceInspection
     */
    public function up()
    {
        $conn = Schema::connection(env('DB_CONNECTION', 'mysql'));

        $conn->create('user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id')->comment('Account账户id');
            $table->unsignedTinyInteger('status')->default(UserStatus::NORMAL)->comment('状态 正常 锁定 禁用');
            $table->string('name', 20)->comment('昵称');
            $table->string('avatar', 255)->nullable()->comment('头像');
            $table->string('fake_name', 20)->nullable()->comment('虚拟昵称');
            $table->string('fake_avatar', 255)->nullable()->comment('虚拟头像');
            $table->unsignedTinyInteger('gender')->default(UserGender::UNKNOWN)->comment('性别：男、女、未知');
            $table->unsignedInteger('level')->default(1)->comment('等级');
            $table->unsignedInteger('exp')->default(0)->comment('经验');
            $table->unsignedInteger('score')->default(0)->comment('积分');
            $table->boolean('is_vip')->default(false)->comment('是否 VIP');
            $table->timestamp('vip_expired_at')->nullable()->comment('VIP 到期时间');
            $table->timestamp('name_edited_at')->nullable()->comment('昵称上次修改时间');
            $table->timestamp('locked_at')->nullable()->comment('锁定时间');
            $table->softDeletes();
            $table->timestamps();

            $table->comment('用户表');

            $table->index('account_id');
        });

        $conn->create('user_info', function (Blueprint $table) {
            $table->bigInteger('user_id')->primary()->comment('用户 ID');
            $table->string('region', 50)->nullable()->comment('省-市-区');
            $table->date('birthday')->nullable()->comment('生日');
            $table->string('signs', 3)->nullable()->comment('星座');
            $table->tinyInteger('emotion')->nullable()->comment('情感状态');
            $table->tinyInteger('purpose')->nullable()->comment('交友目的');
            $table->string('description', 50)->nullable()->comment('个人介绍');

            $table->unsignedInteger('follow_count')->default(0)->comment('关注数量');
            $table->unsignedInteger('fans_count')->default(0)->comment('粉丝数量');
            $table->unsignedInteger('feed_count')->default(0)->comment('动态数量');
            $table->unsignedInteger('comment_count')->default(0)->comment('评论数量');
            $table->unsignedInteger('add_box_count')->default(0)->comment('放盲盒数量');
            $table->unsignedInteger('get_box_count')->default(0)->comment('拆盲盒数量');
            $table->unsignedInteger('visit_count')->default(0)->comment('来访数量');

            $table->comment('用户详细信息表');

            $table->timestamps();
        });

        $conn->create('user_info_review', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('用户 ID');
            $table->tinyInteger('type')->default(UserReviewType::NAME)->comment('资料类型');
            $table->string('value', 255)->comment('修改后的数据');
            $table->timestamps();

            $table->comment('用户信息审核表');
        });

        $conn->create('score_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('用户 ID');
            $table->unsignedTinyInteger('type')->default(ScoreLogType::INCREMENT)->comment('类型 1增加 2 消耗');
            $table->unsignedTinyInteger('score')->comment('积分');
            $table->string('description', 100)->default('')->comment('任务描述');
            $table->timestamps();

            $table->comment('积分金币记录表');

            $table->index(['user_id', 'created_at']);
        });

        $conn->create('user_follow', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('following_id')->comment('被关注者 ID');
            $table->bigInteger('follower_id')->comment('关注者 ID');
            $table->timestamps();

            $table->comment('用户关注粉丝表');

            $table->index(['following_id']);
            $table->index(['follower_id', 'following_id']);
            $table->index(['following_id', 'follower_id']);
        });

        $conn->create('user_visit', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->comment('被访问 ID');
            $table->bigInteger('visitor_id')->comment('访客 ID');
            $table->bigInteger('visit_count')->comment('访问次数');
            $table->timestamps();

            $table->comment('用户关注粉丝表');

            $table->index(['user_id', 'visitor_id']);
        });

        $conn->create('account', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('password', 60)->nullable()->comment('密码');
            $table->unsignedTinyInteger('register_type')->comment('注册时使用的方式');
            $table->unsignedTinyInteger('register_client_type')->comment('注册时使用的客户端');
            $table->unsignedInteger('login_count')->nullable()->comment('登录次数');
            $table->string('login_ip', 15)->nullable()->comment('最后登录ip');
            $table->string('register_ip', 15)->nullable()->comment('注册时使用的ip');
            $table->timestamp('logined_at')->nullable()->comment('最后登录时间');
            $table->softDeletes();
            $table->timestamps();

            $table->comment('账号表');

            $table->index(['deleted_at']);
        });

        $conn->create('account_phone', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('account_id')->comment('账号 ID');
            $table->string('phone', 20)->nullable()->comment('手机号');
            $table->boolean('is_active')->comment('正在使用的 0:解绑 1:绑定中');
            $table->unsignedInteger('login_count')->nullable()->comment('使用此手机号登录的次数');
            $table->timestamp('logined_at')->nullable()->comment('最后登录时间');
            $table->timestamps();

            $table->comment('账号关联手机表');

            $table->index(['account_id']);
            $table->index(['phone', 'is_active']);
        });

        $conn->create('account_wechat', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id')->comment('账号 ID');
            $table->string('open_id', 64)->comment('小程序 open_id');
            $table->string('mp_open_id', 64)->comment('公众号 open_id');
            $table->string('union_id', 64)->nullable()->comment('union_id');
            $table->unsignedTinyInteger('gender')->default(UserGender::UNKNOWN)->comment('性别');
            $table->string('avatar', 100)->nullable()->comment('头像');
            $table->unsignedInteger('login_count')->comment('登录次数');
            $table->timestamp('logined_at')->nullable()->comment('最后登录时间');
            $table->timestamps();

            $table->comment('账号关联微信表');

            $table->index(['account_id']);
        });

        $conn->create('account_faker', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id')->comment('账号 ID');
            $table->string('open_id', 64)->comment('open_id');
            $table->unsignedTinyInteger('gender')->default(UserGender::UNKNOWN)->comment('性别');
            $table->string('avatar', 100)->nullable()->comment('头像');
            $table->string('access_token', 64)->nullable();
            $table->unsignedInteger('login_count')->comment('登录次数');
            $table->timestamp('logined_at')->nullable()->comment('最后登录时间');
            $table->timestamps();

            $table->comment('账号关联虚拟用户表');

            $table->index(['account_id']);
        });

        $conn->create('login_error_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id')->comment('账号 ID');
            $table->string('account', 45)->nullable()->comment('输入的账号');
            $table->string('password', 45)->nullable()->comment('输入的错误密码');
            $table->unsignedTinyInteger('open_type')->nullable()->comment('登陆方式');
            $table->unsignedTinyInteger('login_type')->nullable()->comment('登陆客户端');
            $table->string('ip', 15)->nullable()->comment('登录IP');
            $table->timestamps();

            $table->comment('登陆失败记录表');
        });

        $conn->create('login_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('account_id')->comment('账号ID');
            $table->unsignedBigInteger('open_id')->nullable()->comment('Account附属表中的id');
            $table->unsignedTinyInteger('open_type')->nullable()->comment('登陆方式');
            $table->unsignedTinyInteger('login_type')->nullable()->comment('登陆客户端');
            $table->string('ip', 15)->nullable()->comment('登录IP');
            $table->timestamps();

            $table->comment('登陆记录表');
        });

        $conn->create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();

            $table->comment('计划任务失败记录表');
        });

        $conn->create('ad_category', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 60)->comment('广告位名称');
            $table->string('width')->comment('广告位宽度');
            $table->string('height')->comment('广告位宽度');
            $table->tinyInteger('status')->default(0)->comment('是否开启 0:未开启 1:已开启');
            $table->timestamps();

            $table->comment('广告位');
        });

        $conn->create('ad', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 60)->comment('广告名称');
            $table->unsignedBigInteger('category_id')->default(0)->comment('广告位 ID');
            $table->tinyInteger('status')->default(0)->comment('是否开启 0:未开启 1:已开启');
            $table->string('image')->comment('广告图');
            $table->string('url')->comment('广告链接');
            $table->tinyInteger('target')->default(0)->comment('打开方式 0:当前页面 1:新页面');
            $table->unsignedInteger('view_count')->default(0)->comment('广告展示次数');
            $table->unsignedInteger('click_count')->default(0)->comment('广告点击次数');
            $table->timestamp('before')->nullable()->comment('开始时间');
            $table->timestamp('after')->nullable()->comment('结束时间');
            $table->timestamps();

            $table->comment('广告');
        });

        $conn->create('article', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->comment('文章标题');
            $table->tinyInteger('status')->default(0)->comment('状态');
            $table->integer('category_id')->default(0)->comment('文章分类');
            $table->text('content')->comment('内容');
            $table->timestamps();

            $table->comment('文章表');
        });

        $conn->create('article_category', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('分类名称');
            $table->integer('parent_id')->default(0)->comment('上级 ID');
            $table->integer('order')->default(0)->comment('分类排序');
            $table->boolean('is_directory')->comment('是否根目录');
            $table->unsignedInteger('level')->comment('第几级分类');
            $table->string('cate_path')->comment('分类路径');
            $table->timestamps();

            $table->comment('文章分类表');
        });

        $conn->create('sensitive_words', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('关键字');
            $table->tinyInteger('status')->default(0)->comment('是否开启 0:未开启 1:已开启');
            $table->unsignedInteger('count')->default(0)->comment('命中次数');
            $table->timestamps();

            $table->comment('敏感词库');
        });

        $conn->create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('notice_type')->nullable()->comment('通知类型:系统消息,互动通知');
            $table->integer('event')->nullable()->comment('事件');
            $table->morphs('notifiable');
            $table->json('data')->comment('触发者->触发的主体->触发结果');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('last_at')->nullable();
            $table->timestamps();

            $table->comment('通知表');

            $table->index(['notifiable_type', 'notifiable_id', 'notice_type', 'created_at'], 'index_1');
        });

        $conn->create('subscription', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index()->comment('用户 ID');
            $table->morphs('subscribable');
            $table->timestamps();

            $table->comment('关注订阅表');

            $table->index(['user_id', 'subscribable_type', 'created_at']);
        });

        $conn->create('topic', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('group_id')->nullable()->comment('分组 ID');
            $table->string('name', 30)->comment('话题名称');
            $table->string('description', 1000)->nullable()->comment('话题介绍');
            $table->string('cover', 255)->nullable()->comment('封面');
            $table->unsignedInteger('follow_count')->default(0)->comment('关注数');
            $table->unsignedInteger('feed_count')->default(0)->comment('动态数');
            $table->timestamps();

            $table->comment('话题表');

            $table->index(['group_id', 'feed_count', 'follow_count']);
        });

        $conn->create('topic_group', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 30)->comment('分组名称');
            $table->integer('parent_id')->default(0)->comment('上级 ID');
            $table->integer('order')->default(0)->comment('分类排序');
            $table->timestamps();

            $table->comment('话题分组');
        });

        $conn->create('feed_topic_relation', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('feed_id')->comment('动态 ID');
            $table->unsignedBigInteger('topic_id')->comment('话题 ID');
            $table->timestamps();

            $table->comment('动态话题 关联表');

            $table->index(['topic_id']);
            $table->index(['feed_id']);

            $table->index(['topic_id', 'created_at']);
        });

        $conn->create('like', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('user_id');
            $table->morphs('likeable');
            $table->timestamps();

            $table->comment('点赞表');

            $table->index(['user_id', 'likeable_id', 'likeable_type']);
        });

        $conn->create('feed', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('用户 ID');
            $table->unsignedTinyInteger('status')->default(FeedStatus::PENDING)->comment('状态 待审核,通过,禁止');
            $table->string('location', 10)->nullable()->comment('位置');
            $table->unsignedInteger('score')->default(0)->comment('得分 用于排序');
            $table->unsignedInteger('view_count')->default(0)->comment('阅读数');
            $table->unsignedInteger('like_count')->default(0)->comment('点赞数');
            $table->unsignedInteger('comment_count')->default(0)->comment('评论数');
            $table->softDeletes();
            $table->timestamps();

            $table->comment('用户动态');

            $table->index(['score']);
            $table->index(['deleted_at', 'status', 'created_at']);
            $table->index(['deleted_at', 'user_id', 'created_at']);
        });

        $conn->create('feed_content', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('feed_id')->comment('动态 ID');
            $table->string('text', 5000)->comment('内容');
            $table->timestamps();

            $table->comment('动态内容');

            $table->index(['feed_id']);
        });

        $conn->create('feed_image', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('用户 ID');
            $table->unsignedBigInteger('feed_id')->comment('动态 ID');
            $table->tinyInteger('width')->default(0)->comment('图片宽度');
            $table->tinyInteger('height')->default(0)->comment('图片高度');
            $table->unsignedTinyInteger('status')->default(FeedImageStatus::PENDING)->comment('状态 待审核,通过,禁止');
            $table->string('path', 255)->comment('图片路径');
            $table->timestamps();

            $table->comment('动态图片');

            $table->index(['feed_id', 'status']);
        });

        $conn->create('comment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('用户 ID');
            $table->unsignedBigInteger('feed_id')->comment('动态 ID');
            $table->unsignedTinyInteger('status')->comment('状态 0:正常 1:禁止');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('父评论 ID');
            $table->unsignedBigInteger('reply_id')->nullable()->comment('被回复的评论 ID');
            $table->unsignedTinyInteger('level')->comment('评论等级 0:评论 1:回复');
            $table->unsignedInteger('like_count')->comment('点赞数');
            $table->unsignedInteger('reply_count')->comment('评论回复数');
            $table->softDeletes();
            $table->timestamps();

            $table->comment('动态评论表');

            $table->index(['user_id']);
            $table->index(['parent_id']);
            $table->index(['feed_id', 'level']);
        });

        $conn->create('comment_content', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('comment_id')->comment('动态 ID');
            $table->string('text', 500)->comment('评论内容');
            $table->timestamps();

            $table->comment('评论内容');

            $table->index(['comment_id']);
        });

        $conn->create('comment_image', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('用户 ID');
            $table->unsignedBigInteger('comment_id')->comment('评论 ID');
            $table->unsignedInteger('width')->comment('图片宽度');
            $table->unsignedInteger('height')->comment('图片高度');
            $table->string('path', 255)->comment('图片路径');
            $table->timestamps();

            $table->comment('动态评论图片表');

            $table->index(['comment_id']);
        });

        $conn->create('chat_conversation', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sender')->comment('发送者 ID');
            $table->unsignedBigInteger('receiver')->comment('接收者 ID');
            $table->boolean('private')->default(true)->comment('是否为私信');
            $table->json('data')->nullable()->comment('额外数据');
            $table->timestamps();

            $table->comment('消息对话表');

            $table->index(['sender', 'receiver']);
        });

        $conn->create('chat_message', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('conversation_id')->comment('对话 ID');
            $table->unsignedBigInteger('sender')->comment('发送者 ID');
            $table->tinyInteger('type')->default(ChatMessageType::TEXT)->comment('消息类型');
            $table->text('content')->comment('消息内容');
            $table->json('extra')->nullable()->comment('额外数据');
            $table->timestamps();

            $table->comment('消息聊天记录表');

            $table->index(['conversation_id']);
        });

        $conn->create('chat_message_notification', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('conversation_id')->comment('对话 ID');
            $table->unsignedBigInteger('message_id')->comment('消息 ID');
            $table->unsignedBigInteger('sender')->comment('发送者 ID');
            $table->unsignedBigInteger('receiver')->comment('接收者 ID');
            $table->boolean('private')->default(true)->comment('冗余对话类型');
            $table->unsignedInteger('unread_count')->comment('未读消息数量');
            $table->timestamp('read_at')->nullable()->comment('阅读时间');
            $table->timestamp('rejoined_at')->nullable()->comment('重新加入对话时间');
            $table->softDeletes();
            $table->timestamps();

            $table->comment('消息通知表');

            $table->index(['sender', 'conversation_id', 'deleted_at'], 'index_1');
            $table->index(['sender', 'receiver', 'conversation_id', 'deleted_at'], 'index_2');
            $table->index(['sender', 'private', 'deleted_at', 'unread_count'], 'index_3');
            $table->index(['receiver', 'private', 'deleted_at'], 'index_4');
        });

        $conn->create('report', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('用户 ID');
            $table->unsignedTinyInteger('reason')->comment('举报原因');
            $table->morphs('resources');
            $table->string('content', 150)->comment('举报详细内容');
            $table->timestamps();

            $table->comment('举报表');

            $table->index(['user_id', 'resources_id', 'resources_type']);
        });

        $conn->create('vip_order', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('用户 ID');
            $table->string('no', 255)->unique()->comment('订单号');
            $table->tinyInteger('type')->default(VipOrderType::USER)->comment('订单类型 区分用户下单和系统赠送');
            $table->tinyInteger('vip_slug')->comment('vip 类型标示');
            $table->string('description', 255)->nullable()->comment('商品描述');
            $table->decimal('amount', 10, 2)->comment('订单总金额');
            $table->tinyInteger('closed')->comment('订单是否关闭');
            $table->string('remark', 255)->nullable()->comment('订单备注');
            $table->string('payment_no', 255)->nullable()->comment('支付订单号');
            $table->timestamp('payment_at')->nullable()->comment('支付时间');
            $table->timestamps();

            $table->comment('VIP 订单表');
        });

        $conn->create('task_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('用户 ID');
            $table->tinyInteger('action_slug')->default(0)->comment('任务 ID');
            $table->tinyInteger('exp')->default(0)->comment('经验');
            $table->string('description', 100)->default('')->comment('任务描述');
            $table->timestamps();

            $table->comment('任务记录表');

            $table->index(['user_id', 'action_slug']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $conn = Schema::connection(env('DB_CONNECTION', 'mysql'));

        $conn->dropIfExists('user');
        $conn->dropIfExists('user_info');
        $conn->dropIfExists('user_follow');
        $conn->dropIfExists('user_visit');
        $conn->dropIfExists('score_log');
        $conn->dropIfExists('account');
        $conn->dropIfExists('account_phone');
        $conn->dropIfExists('account_wechat');
        $conn->dropIfExists('account_faker');
        $conn->dropIfExists('login_error_log');
        $conn->dropIfExists('login_log');
        $conn->dropIfExists('failed_jobs');
        $conn->dropIfExists('ad_category');
        $conn->dropIfExists('ad');
        $conn->dropIfExists('article');
        $conn->dropIfExists('article_category');
        $conn->dropIfExists('sensitive_words');
        $conn->dropIfExists('notifications');
        $conn->dropIfExists('region');
        $conn->dropIfExists('subscription');
        $conn->dropIfExists('topic');
        $conn->dropIfExists('topic_group');
        $conn->dropIfExists('feed_topic_relation');
        $conn->dropIfExists('like');
        $conn->dropIfExists('feed');
        $conn->dropIfExists('feed_content');
        $conn->dropIfExists('feed_image');
        $conn->dropIfExists('comment');
        $conn->dropIfExists('comment_content');
        $conn->dropIfExists('comment_image');
        $conn->dropIfExists('chat_conversation');
        $conn->dropIfExists('chat_message');
        $conn->dropIfExists('chat_message_notification');
        $conn->dropIfExists('report');
        $conn->dropIfExists('vip_order');
        $conn->dropIfExists('task_log');
    }
}
