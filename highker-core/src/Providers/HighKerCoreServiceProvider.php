<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Providers;

use AlibabaCloud\Client\AlibabaCloud;
use HighKer\Core\Console\Cron\CheckFakeAvatarCommand;
use HighKer\Core\Console\Cron\JimuSpiderCommand;
use HighKer\Core\Console\Cron\SetUserInactiveCommand;
use HighKer\Core\Console\Cron\TestCommand;
use HighKer\Core\Console\Cron\VipExpiredCommand;
use HighKer\Core\Console\HighKerCommand;
use HighKer\Core\Console\InstallCommand;
use HighKer\Core\Console\PublishCommand;
use HighKer\Core\Console\SeedCommand;
use HighKer\Core\Console\UninstallCommand;
use HighKer\Core\Listeners\CompressNotification;
use HighKer\Core\Middleware\CheckGender;
use HighKer\Core\Middleware\ForbiddenUser;
use HighKer\Core\Middleware\LockedUser;
use HighKer\Core\Middleware\OnlineUser;
use HighKer\Core\Middleware\ReturnJson;
use HighKer\Core\Support\FileDriver\ScsAdapter;
use HighKer\Core\Support\SaeChannel;
use HighKer\Core\Support\SensitiveFilter;
use HighKer\Core\Support\Wechat\MiniApp;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;

/**
 * Class HighKerCoreServiceProvider.
 */
class HighKerCoreServiceProvider extends ServiceProvider
{
    /**
     * 定义自定义artisan命令的路径.
     */
    protected array $commands = [
        HighKerCommand::class,
        InstallCommand::class,
        UninstallCommand::class,
        PublishCommand::class,
        SeedCommand::class,

        TestCommand::class,
        JimuSpiderCommand::class,
        SetUserInactiveCommand::class,
        VipExpiredCommand::class,
        CheckFakeAvatarCommand::class,
    ];

    /**
     * 组件需要注入的中间件.
     */
    protected array $routeMiddleware = [
        'forbidden_user' => ForbiddenUser::class,
        'locked_user'    => LockedUser::class,
        'online_user'    => OnlineUser::class,
        'check_gender'   => CheckGender::class,
        'return_json'    => ReturnJson::class,
    ];

    /**
     * 需要注入的中间件组.
     *
     * @var string[]
     */
    protected array $middlewareGroups = [
        'highker' => [
            OnlineUser::class,
            ReturnJson::class,
        ],
    ];

    /**
     * 需要监听的 事件.
     *
     * @var string[]
     */
    protected array $listenEvent = [
        'Illuminate\Notifications\Events\NotificationSent' => CompressNotification::class,
    ];

    /**
     * Boot the service provider.
     */
    public function boot()
    {
        // 注册路由
        $this->registerRoutes();

        // 注册路由中间件
        $this->registerRouteMiddleware();

        // 注册 命令行
        $this->commands($this->commands);

        // 注册 事件监听
        $this->registerListenEvent();

        // 注册发布配置文件方法
        $this->registerPublishing();

        // 注册 数据库 sql 监听
        $this->registerListenDb();

        // 注册 任务调度
        // $this->registerSchedule();

        // 文件系统自定义
        app('filesystem')->extend('scs', function ($app, $config) {
            $adapter = new ScsAdapter(
                $config['access_key'],
                $config['secret_key'],
                $config['bucket'],
                $config['domain']
            );

            return new FilesystemAdapter(new Filesystem($adapter), $adapter, $config);
        });
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerServe();
    }

    /**
     * 路由注入.
     */
    protected function registerRoutes()
    {
        $this->loadRoutesFrom(__DIR__.'/../Route/ApiRouter.php');
        $this->loadRoutesFrom(__DIR__.'/../Route/AdminRouter.php');
    }

    /**
     * 资源发布注册.
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../../config' => config_path()], 'config');
            $this->publishes([__DIR__.'/../../database/factories' => database_path('factories')], 'factories');
            $this->publishes([__DIR__.'/../../database/migrations' => database_path('migrations')], 'migrations');
            $this->publishes([__DIR__.'/../../database/seeders' => database_path('seeders')], 'seeders');
            $this->publishes([__DIR__.'/../../resources' => resource_path()], 'resources');
        }
    }

    /**
     * 注册路由中间件.
     */
    protected function registerRouteMiddleware()
    {
        /** @var Router $router */
        $router = app('router');
        // 注册 路由中间件
        foreach ($this->routeMiddleware as $key => $middleware) {
            $router->aliasMiddleware($key, $middleware);
        }

        // 注册 中间件组
        foreach ($this->middlewareGroups as $group => $groupMiddleware) {
            $router->middlewareGroup($group, $groupMiddleware);
        }
    }

    /**
     * 注册 事件监听器.
     */
    protected function registerListenEvent()
    {
        foreach ($this->listenEvent as $event => $listeners) {
            app('events')->listen($event, $listeners);
        }
    }

    /**
     * 注册 Sql 打印监听.
     */
    protected function registerListenDb()
    {
        // 只在本地开发环境启用 SQL 日志
        if (app()->environment('local')) {
            DB::listen(
                function ($query) {
                    $sqlWithPlaceholders = str_replace(['%', '?', '%s%s'], ['%%', '%s', '?'], $query->sql);

                    $bindings = $query->connection->prepareBindings($query->bindings);
                    $pdo = $query->connection->getPdo();
                    $realSql = $sqlWithPlaceholders;

                    if (count($bindings) > 0) {
                        $realSql = vsprintf($sqlWithPlaceholders, array_map([$pdo, 'quote'], $bindings));
                    }

                    Log::channel('sql')->info(PHP_EOL.$realSql.PHP_EOL.request()->method().' | '.request()->getRequestUri().PHP_EOL);
                }
            );
        }
    }

    /**
     * 注册 任务调度.
     */
    protected function registerSchedule()
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            // 测试 Cron
            $schedule->command('corn:test')->hourly();
            // 站内 VIP 检测过期并设置 每天凌晨 5 点执行
            $schedule->command('corn:vip-expired')->dailyAt('05:00');
            // telescope 删除 48 小时前创建的所有记录
            $schedule->command('telescope:prune --hours=48')->daily();
            // Horizon 指标控制面板 队列的等待时间和吞吐量等信息 每五分钟运行一次
            $schedule->command('horizon:snapshot')->everyFiveMinutes();
            $schedule->command('corn:set-user-inactive')->everyMinute();
        });
    }

    /**
     * 注册 新浪云 webSocket.
     */
    protected function registerServe()
    {
        // 注册 新浪云 webSocket.
        $this->app->singleton('saeChannel', function () {
            return new SaeChannel();
        });

        // 注册 自己实现的 微信接口.
        $this->app->bind('miniApp', function () {
            return new MiniApp();
        });

        // 注册 敏感词 服务
        $this->app->singleton('sensitiveKeywordFilter', function () {
            return new SensitiveFilter();
        });

        // 注册 阿里巴巴 SDK.
        // $this->app->singleton('alibabaSdk', function () {
        //     return AlibabaCloud::accessKeyClient(env('ALI_ACCESS_KEY'), env('ALI_SECRET_KEY'))->regionId('cn-hangzhou')->asDefaultClient();
        // });
    }
}
