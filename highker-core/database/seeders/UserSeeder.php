<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Seeders;

use Faker\Generator as faker;
use HighKer\Core\Enum\AccountRegisterType;
use HighKer\Core\Enum\ClientType;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Models\Account;
use HighKer\Core\Models\AccountBase;
use HighKer\Core\Models\User;
use Illuminate\Database\Seeder;
use Throwable;

class UserSeeder extends Seeder
{
    /**
     * @throws HighKerException
     * @throws Throwable
     */
    public function run()
    {
        $this->createDevUser();

        $this->createUser();

        $this->createFakerUser();
    }

    /**
     * @throws HighKerException
     * @throws Throwable
     */
    private function createDevUser()
    {
        $accountDev = [
            '18101035120' => 'dirty',
            '13612345678' => 'devUser',
        ];

        $devCount = count($accountDev);
        $this->command->getOutput()->progressStart($devCount);
        foreach ($accountDev as $phone => $name) {
            $account = Account::createAccount(
                '12345678',
                '127.0.0.1',
                AccountRegisterType::PHONE,
                ClientType::PC
            );
            // 绑定手机
            $account->bindPhone($phone);
            User::createUser($account->id, $name);
            $this->command->getOutput()->progressAdvance();
        }

        $this->command->getOutput()->writeln('一共生成了 '.$devCount.' 个开发用户');
    }

    /**
     * @throws HighKerException
     * @throws Throwable
     */
    private function createUser()
    {
        $faker = app(faker::class);

        $count = 20;
        $this->command->getOutput()->progressStart($count);
        foreach (range(0, $count - 1) as $i) {
            $phone = '181'.mt_rand(1000, 9999).mt_rand(1000, 9999);

            $account = Account::createAccount(
                '12345678',
                '127.0.0.1',
                AccountRegisterType::PHONE,
                ClientType::PC
            );

            // 绑定手机
            $account->bindPhone($phone);

            User::createUser($account->id, $faker->name);

            $this->command->getOutput()->progressAdvance();
        }
        $this->command->getOutput()->progressFinish();
        $this->command->getOutput()->writeln('一共生成了 '.$count.' 个用户');
    }

    /**
     * @throws HighKerException
     * @throws Throwable
     */
    private function createFakerUser()
    {
        $fakerUser = [
            [
                'name'   => '郭宸🐷',
                'avatar' => 'user_avatar/2020-08-05/5f2a50b51a5bb.jpg',
                'gender' => 1,
            ],
            [
                'name'   => '林欢👈🏿',
                'avatar' => 'user_avatar/2020-08-05/5f2a50ca93eaa.jpg',
                'gender' => 1,
            ],
            [
                'name'   => '緝',
                'avatar' => 'user_avatar/2020-08-05/5f2a50dde91b1.jpg',
                'gender' => 0,
            ],
            [
                'name'   => '辰兮殿下【兮爷】',
                'avatar' => 'user_avatar/2020-08-05/5f2a50e4a309b.jpg',
                'gender' => 2,
            ],
            [
                'name'   => '眼底星碎~',
                'avatar' => 'user_avatar/2020-08-05/5f2a50e90bfb1.jpg',
                'gender' => 2,
            ],
            [
                'name'   => 'L♥VE',
                'avatar' => 'user_avatar/2020-08-05/5f2a50ed02011.jpg',
                'gender' => 2,
            ],
            [
                'name'   => '今溪喵',
                'avatar' => 'user_avatar/2020-08-05/5f2a50f2d84b4.jpg',
                'gender' => 2,
            ],
            [
                'name'   => '捏捏肥脸•ᴥ•',
                'avatar' => 'user_avatar/2020-08-05/5f2a50f824597.jpg',
                'gender' => 2,
            ],
            [
                'name'   => '九欤',
                'avatar' => 'user_avatar/2020-08-05/5f2a50fc9256d.jpg',
                'gender' => 2,
            ],
            [
                'name'   => 'M牧',
                'avatar' => 'user_avatar/2020-08-05/5f2a5100cf376.jpg',
                'gender' => 1,
            ],
            [
                'name'   => 'এ̶灿灿很奈斯এ̶',
                'avatar' => 'user_avatar/2020-08-05/5f2a5104c0768.jpg',
                'gender' => 2,
            ],
            [
                'name'   => '幼稚园的兮兮.',
                'avatar' => 'user_avatar/2020-08-05/5f2a5109caf6b.jpg',
                'gender' => 2,
            ],
            [
                'name'   => '苏醒ト',
                'avatar' => 'user_avatar/2020-08-05/5f2a510e3399d.jpg',
                'gender' => 2,
            ],
            [
                'name'   => '.油炸小朋友.🍟',
                'avatar' => 'user_avatar/2020-08-05/5f2a5114563b7.jpg',
                'gender' => 2,
            ],
            [
                'name'   => '⁡⁢⁢善⁡意',
                'avatar' => 'user_avatar/2020-08-05/5f2a51193c75e.jpg',
                'gender' => 1,
            ],
            [
                'name'   => '妹妹36D.',
                'avatar' => 'user_avatar/2020-08-05/5f2a511db9856.jpg',
                'gender' => 2,
            ],
            [
                'name'   => '应为我从未离开',
                'avatar' => 'user_avatar/2020-08-05/5f2a512275c63.jpg',
                'gender' => 1,
            ],
            [
                'name'   => '一辞.k',
                'avatar' => 'user_avatar/2020-08-05/5f2a5127c210a.jpg',
                'gender' => 1,
            ],
            [
                'name'   => '锦怜',
                'avatar' => 'user_avatar/2020-08-05/5f2a512ca09c0.jpg',
                'gender' => 1,
            ],
            [
                'name'   => '爱哭包小苏.',
                'avatar' => 'user_avatar/2020-08-05/5f2a5136091c9.jpg',
                'gender' => 2,
            ],
            [
                'name'   => '一〇二',
                'avatar' => 'user_avatar/2020-08-05/5f2a513bef4a6.jpg',
                'gender' => 2,
            ],
            [
                'name'   => '💎⚔️⛓',
                'avatar' => 'user_avatar/2020-08-05/5f2a51409a79a.jpg',
                'gender' => 2,
            ],
            [
                'name'   => '小悦不乖ˇ',
                'avatar' => 'user_avatar/2020-08-05/5f2a514554725.jpg',
                'gender' => 2,
            ],
            [
                'name'   => '整神⁣',
                'avatar' => 'user_avatar/2020-08-05/5f2a514c31448.jpg',
                'gender' => 0,
            ],
            [
                'name'   => '奶油甜泡芙.',
                'avatar' => 'user_avatar/2020-08-05/5f2a51528dafd.jpg',
                'gender' => 2,
            ],
            [
                'name'   => '也罢',
                'avatar' => 'user_avatar/2020-08-05/5f2a5159d3626.jpg',
                'gender' => 0,
            ],
            [
                'name'   => '迷夜',
                'avatar' => 'user_avatar/2020-08-05/5f2a516010951.jpg',
                'gender' => 2,
            ],
        ];

        $count = count($fakerUser);
        $this->command->getOutput()->progressStart($count);
        foreach ($fakerUser as $key => $user) {
            // 创建账号
            $account = Account::createAccount('12345678', '127.0.0.1', AccountRegisterType::FAKER, ClientType::PC);

            /** @noinspection PhpUnhandledExceptionInspection */
            $openAccount = AccountBase::createOpen($account->id, $account->id, AccountRegisterType::FAKER, $user);

            // 创建用户
            User::createUser($account->id, $user['name'], $user['gender'], $user['avatar']);

            $this->command->getOutput()->progressAdvance();
        }
        $this->command->getOutput()->progressFinish();
        $this->command->getOutput()->writeln('一共生成了 '.$count.' 个用户');
    }
}
