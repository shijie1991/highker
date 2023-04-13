<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Traits;

use HighKer\Core\Enum\ResponseCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait ApiResponseTrait.
 *
 * 说明#
 * 整体响应结构设计参考如上，相对严格地遵守了 RESTful 设计准则，返回合理的 HTTP 状态码。
 *
 * 考虑到业务通常需要返回不同的 “业务描述处理结果”，在所有响应结构中都支持传入符合业务场景的 message
 *
 * data:
 * 查询单条数据时直接返回对象结构，减少数据层级；
 * 查询列表数据时返回数组结构；
 * 创建或更新成功，返回修改后的数据；（也可以不返回数据直接返回空对象）
 * 删除成功时返回空对象
 *
 * status:
 * error, 客户端（前端）出错，HTTP 状态响应码在 400-599 之间。如，传入错误参数，访问不存在的数据资源等
 * fail，服务端（后端）出错，HTTP 状态响应码在 500-599 之间。如，代码语法错误，空对象调用函数，连接数据库失败，undefined index 等
 * success, HTTP 响应状态码为 1XX、2XX 和 3XX，用来表示业务处理成功。
 * message: 描述执行的请求操作处理的结果；也可以支持国际化，根据实际业务需求来切换。
 * code: HTTP 响应状态码；可以根据实际业务需求，调整成业务操作码
 */
trait ApiResponseTrait
{
    /**
     * @param     $data
     * @param int $option
     *
     * @return JsonResource|JsonResponse
     */
    public function success($data, string $message = '', int $code = ResponseCode::OK, array $headers = [], $option = 0)
    {
        $message = (!$message && isset(ResponseCode::MAP[$code])) ? ResponseCode::MAP[$code] : $message;
        $additionalData = [
            'status'  => 'success',
            'code'    => $code,
            'message' => $message,
        ];

        if ($data instanceof JsonResource) {
            $additionalData = array_merge($additionalData, $data->additional);

            return $data->additional($additionalData);
        }

        $data = $data ?: (object) $data;

        return response()->json(array_merge($additionalData, ['data' => $data]), Response::HTTP_OK, $headers, $option);
    }

    public function fail(
        string $message = '',
        int $code = ResponseCode::SYSTEM_ERROR,
        $data = null,
        array $header = [],
        int $options = 0
    ) {
        $message = (!$message && isset(ResponseCode::MAP[$code])) ? ResponseCode::MAP[$code] : $message;

        return response()->json([
            'status'  => 'error',
            'code'    => $code,
            'message' => $message,
            'data'    => (object) $data,
        ], Response::HTTP_OK, $header, $options)->throwResponse();
    }
}
