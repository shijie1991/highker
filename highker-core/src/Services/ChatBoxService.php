<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Services;

use Exception;
use HighKer\Core\Enum\ChatMessageType;
use HighKer\Core\Enum\ResponseCode;
use HighKer\Core\Enum\UserGender;
use HighKer\Core\Enum\UserRanking;
use HighKer\Core\Enum\UserTask;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\TaskLog;
use HighKer\Core\Models\User;
use HighKer\Core\Requests\ChatBoxCreateRequest;
use HighKer\Core\Support\Facades\Chat;
use HighKer\Core\Support\HighKer;
use HighKer\Core\Support\Ranking;
use HighKer\Core\Utils\ImageUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ChatBoxService
{
    /**
     * 获取盲盒.
     *
     * @return Model
     *
     * @throws HighKerException
     */
    public static function getChatBox()
    {
        $prerogative = User::getPrerogativeCount(Auth::user());

        if ($prerogative['used']['get_box_count'] >= $prerogative['all']['get_box_count']) {
            throw new HighKerException('拆盲盒次数不足');
        }

        [$getKey, $expire] = Highker::getCacheKey('user:box', 'get-count', [now()->toDateString(), Auth::id()]);

        // 已捞取过的用户记录
        [$logKey] = Highker::getCacheKey('user:box', 'get-log', [Auth::id()]);

        // 按性别分为 两个池子
        $gender = Auth::user()->gender == UserGender::MALE ? UserGender::FEMALE : UserGender::MALE;
        [$genderKey] = Highker::getCacheKey('user:box', 'list-gender', [$gender]);

        // 判断 是否已经捞取过 该用户的瓶子
        $diff = Redis::sdiff([$genderKey, $logKey]);

        if (!$diff) {
            throw new HighKerException(ResponseCode::MAP[ResponseCode::BOX_NOT_FOND], ResponseCode::BOX_NOT_FOND);
        }

        $userId = Arr::random($diff);

        // 从队列获取 第一个
        [$dataKey] = Highker::getCacheKey('user:box', 'box-data', [$userId]);
        $boxData = Redis::lindex($dataKey, Redis::llen($dataKey) - 1);

        if (!$boxData) {
            throw new HighKerException('系统错误,稍后再试');
        }

        $boxData = json_decode($boxData, true);

        $user = User::query()->find($userId);

        $db = HighKer::db();
        $db->beginTransaction();

        try {
            // 创建盲盒对话
            $conversation = Chat::createConversation($user, Auth::user());

            // 发送消息
            $message = Chat::message($boxData['content'])->type($boxData['type'])->from($user)->to($conversation)->extra($boxData['extra'])->send();

            // 记录冗余 捞瓶子数量
            Auth::user()->info()->increment('get_box_count');

            $db->commit();

            // 每日任务 拆盲盒
            TaskLog::dailyTask(UserTask::GET_BOX);

            // 弹出已消费的队列
            Redis::rpop($dataKey);

            // 记录捞瓶子次数
            Redis::incr($getKey);
            Redis::expire($getKey, $expire);

            // 记录捞取的用户(尽可能防止重复捞取同一用户)
            Redis::sadd($logKey, $userId);

            // 如果队列已经没有数据 则删除集合中的成员
            if (Redis::llen($dataKey) == 0) {
                Redis::srem($genderKey, $userId);
            }

            Ranking::incrementRank(UserRanking::GET_BOX, Auth::user());
        } catch (Throwable $e) {
            $db->rollBack();

            throw new HighKerException($e->getMessage());
        }

        return $message;
    }

    /**
     * @throws HighKerException
     * @throws Exception
     */
    public static function createChatBox(ChatBoxCreateRequest $request)
    {
        $prerogative = User::getPrerogativeCount(Auth::user());

        if ($prerogative['used']['add_box_count'] >= $prerogative['all']['add_box_count']) {
            throw new HighKerException('放盲盒次数不足');
        }

        [$addKey, $expire] = Highker::getCacheKey('user:box', 'add-count', [now()->toDateString(), Auth::id()]);

        $type = null;
        if ($request->has('content')) {
            $type = ChatMessageType::TEXT;
        }
        if ($request->has('image')) {
            $type = ChatMessageType::IMAGE;
        }
        if ($request->has('voice')) {
            $type = ChatMessageType::VOICE;
        }

        $extra = null;
        $content = '';
        if ($type == ChatMessageType::TEXT) {
            $content = $request->input('content');
        }

        if ($type == ChatMessageType::IMAGE) {
            // 获取 图片 尺寸
            [$width, $height] = ImageUtils::getSize($request->file('image'));

            $extra = [
                'image' => [
                    'width'  => $width,
                    'height' => $height,
                ],
            ];

            $content = Storage::putFile(Highker::uploadDir('chat'), $request->file('image'));
        }

        if ($type == ChatMessageType::VOICE) {
            $extra = ['duration' => $request->input('duration')];
            $content = Storage::putFile(Highker::uploadDir('chat'), $request->file('voice'));
        }

        $data = [
            'type'    => $type,
            'extra'   => $extra,
            'content' => $content,
        ];

        // 将数据 格式化为 json
        $data = json_encode($data);

        // 存储盲盒详细内容
        [$dataKey] = Highker::getCacheKey('user:box', 'box-data', [Auth::id()]);
        // 按性别分为 两个池子
        [$genderKey] = Highker::getCacheKey('user:box', 'list-gender', [Auth::user()->gender]);

        $boxData = [$data];
        // 如果是女生 扔一个相当于扔 4 个
        if (Auth::user()->gender == UserGender::FEMALE) {
            $boxData = array_fill(0, 4, $data);
        }

        foreach ($boxData as $data) {
            Redis::lpush($dataKey, $data);
        }

        Redis::sadd($genderKey, Auth::user()->id);

        // 记录冗余 扔瓶子数量
        Auth::user()->info()->increment('add_box_count');

        // 今日扔瓶子次数+1
        Redis::incr($addKey);
        Redis::expire($addKey, $expire);

        // 每日任务 放盲盒
        TaskLog::dailyTask(UserTask::ADD_BOX);

        Ranking::incrementRank(UserRanking::ADD_BOX, Auth::user());
    }
}
