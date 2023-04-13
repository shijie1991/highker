<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Support\Wechat;

use EasyWeChat\Kernel\Contracts\Server;
use EasyWeChat\Kernel\Exceptions\BadResponseException;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\HttpClient\AccessTokenAwareClient;
use EasyWeChat\MiniApp\Application;
use Overtrue\LaravelWeChat\EasyWeChat;
use ReflectionException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Throwable;

class MiniApp
{
    public Application $app;
    public AccessTokenAwareClient $api;

    public function __construct()
    {
        $this->app = EasyWeChat::miniApp();
        $this->api = $this->app->getClient();
    }

    /**
     * @return \EasyWeChat\OfficialAccount\Server|Server
     *
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws Throwable
     */
    public function getServer()
    {
        return $this->app->getServer();
    }

    public function getUtils()
    {
        return $this->app->getUtils();
    }

    /**
     * @return array
     *
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws BadResponseException|TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function session(string $code)
    {
        $account = $this->app->getAccount();

        return $this->api->get('/sns/jscode2session', [
            'query' => [
                'appid'      => $account->getAppId(),
                'secret'     => $account->getSecret(),
                'js_code'    => $code,
                'grant_type' => 'authorization_code',
            ],
        ])->toArray();
    }

    /**
     * @param $code
     *
     * @return array
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws BadResponseException
     */
    public function getUserPhone($code)
    {
        return $this->api->postJson('/wxa/business/getuserphonenumber', ['code' => $code])->toArray(false);
    }

    /**
     * @param $openId
     * @param $content
     *
     * @return array
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws BadResponseException
     */
    public function checkContent($openId, $content, int $scene = 1, int $version = 2)
    {
        return $this->api->post('/wxa/msg_sec_check', [
            'json' => [
                'version' => $version,
                'scene'   => $scene,
                'openid'  => $openId,
                'content' => $content,
            ],
        ])->toArray();
    }

    /**
     * @param $openId
     * @param $mediaUrl
     * @param $mediaType
     *
     * @return array
     *
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function checkMedia($openId, $mediaUrl, $mediaType, int $scene = 1, int $version = 2)
    {
        return $this->api->post('/wxa/media_check_async', [
            'json' => [
                'scene'      => $scene,
                'version'    => $version,
                'openid'     => $openId,
                'media_url'  => $mediaUrl,
                'media_type' => $mediaType,
            ],
        ])->toArray();
    }
}
