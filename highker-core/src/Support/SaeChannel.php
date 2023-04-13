<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use HighKer\Core\Exceptions\HighKerException;
use Illuminate\Support\Str;

/**
 * Class SaeChannel.
 *
 * @example http://news.sinacloud.com/san-fen-zhong-qing-song-yong-shang-websocketfu-wu/
 * @example https://www.sinacloud.com/doc/api.html#channel-fu-wu-websocket
 */
class SaeChannel
{
    private mixed $accessKey;
    private mixed $secretKey;

    private string $baseUri = 'http://g.sinacloud.com';
    private string $createUri = '/channel/v1/create_channel';
    private string $sendUri = '/channel/v1/send_message';

    public function __construct()
    {
        $this->accessKey = config('core.sae.access_key');
        $this->secretKey = config('core.sae.secret_key');
    }

    /**
     * 创建 Channel.
     *
     * @param string $clientId 通道的标示，数字字符组合唯一
     * @param int    $duration 通道的过期时间单位是秒，最大3600
     *
     * @throws GuzzleException
     * @throws HighKerException
     */
    public function createChannel(string $clientId, int $duration = 3600, bool $https = true): string
    {
        if (!$clientId) {
            throw new HighKerException('clientId 参数不能为空');
        }
        $client = new Client(['base_uri' => $this->baseUri]);

        try {
            $response = $client->post($this->createUri, [
                'headers'     => $this->getHeaders($this->createUri),
                'form_params' => [
                    'client_id' => $clientId,
                    'duration'  => $duration,
                ],
            ]);
            if ($response->getStatusCode() == 200) {
                $result = json_decode($response->getBody()->getContents(), true);
                if (isset($result['errno'])) {
                    throw new HighKerException('Create Channel '.$result['error']);
                }

                return $https ? Str::replaceFirst('ws', 'wss', $result['data']) : $result['data'];
            }
        } catch (ClientException $e) {
            throw new HighKerException($e->getMessage());
        }

        return '';
    }

    /**
     * @param string $clientId 通道的标示，数字字符组合 和创建时一致，例如client-1
     * @param string $message  需要推送的消息 不能为空，最大可以发送4k的消息
     * @param int    $async    0或者1 是否异步发送，如果设置为1，服务端会立刻返回（始终返回成功），默认为0
     *
     * @throws GuzzleException
     * @throws HighKerException
     */
    public function sendMessage(string $clientId, string $message, int $async = 0): bool
    {
        if (!$clientId) {
            throw new HighKerException('clientId 参数不能为空');
        }

        if (!$message) {
            throw new HighKerException('需要推送的消息 不能为空');
        }

        $client = new Client(['base_uri' => $this->baseUri]);

        try {
            $response = $client->post($this->sendUri, [
                'headers'     => $this->getHeaders($this->sendUri),
                'form_params' => [
                    'client_id' => $clientId,
                    'message'   => $message,
                    'async'     => $async,
                ],
            ]);

            if ($response->getStatusCode() == 200) {
                $result = json_decode($response->getBody()->getContents(), true);

                if (isset($result['errno'])) {
                    throw new HighKerException('Channel Send Message '.$result['error']);
                }

                return true;
            }
        } catch (ClientException $e) {
            throw new HighKerException($e->getMessage());
        }

        return true;
    }

    protected function getHeaders($uri, $method = 'POST'): array
    {
        $timeline = now()->timestamp;

        $params[] = $method;
        $params[] = $uri;

        $requestHeaders = ['x-sae-accesskey' => $this->accessKey, 'x-sae-timestamp' => $timeline];
        ksort($requestHeaders);
        foreach ($requestHeaders as $key => $value) {
            $params[] = sprintf('%s:%s', $key, $value);
        }

        $paramsStr = implode("\n", $params);

        $signature = hash_hmac('sha256', $paramsStr, $this->secretKey, true);
        $baseSignature = base64_encode($signature);

        return [
            'x-sae-accesskey' => $this->accessKey,
            'x-sae-timestamp' => $timeline,
            'Authorization'   => sprintf(' SAEV1_HMAC_SHA256 %s', $baseSignature),
        ];
    }
}
