<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Controllers\Api;

use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\User;
use HighKer\Core\Requests\ChatBoxCreateRequest;
use HighKer\Core\Resources\ChatConversationResource;
use HighKer\Core\Resources\CommonResource;
use HighKer\Core\Services\ChatBoxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ChatBoxController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['forbidden_user', 'locked_user', 'check_gender']);
    }

    /**
     * 开盲盒.
     *
     * @throws HighKerException
     *
     * @return JsonResource|JsonResponse
     */
    public function index()
    {
        $message = ChatBoxService::getChatBox();

        return $this->success(ChatConversationResource::make($message), '开启盲盒');
    }

    /**
     * 发送盲盒.
     *
     * @throws HighKerException
     *
     * @return JsonResource|JsonResponse
     */
    public function store(ChatBoxCreateRequest $request)
    {
        ChatBoxService::createChatBox($request);

        return $this->success(null, '盲盒发送成功');
    }

    /**
     * @throws HighKerException
     */
    public function count()
    {
        $prerogative = User::getPrerogativeCount(Auth::user());

        $addBoxCount = $prerogative['all']['add_box_count'] - $prerogative['used']['add_box_count'];
        $getBoxCount = $prerogative['all']['get_box_count'] - $prerogative['used']['get_box_count'];

        $data = [
            'add_box_count' => max($addBoxCount, 0),
            'get_box_count' => max($getBoxCount, 0),
        ];

        return $this->success(CommonResource::make($data));
    }
}
