<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TopicTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        \DB::table('topic')->delete();

        \DB::table('topic')->insert([
            0 => [
                'id'          => 1,
                'group_id'    => 4,
                'name'        => '最靠谱的恋爱',
                'description' => '我们的口号是,进来这个小组,就马上谈段靠谱的恋爱!

速度找人,见面,吃饭!有感觉就谈,没感觉就换!不在网上观望和浪费时间!

┏━┓ 靠谱是需要规矩滴：
┃谈┃A 谨慎异地恋。我们不玩不靠谱的。
┃恋┃B 禁扭捏作态。我们不玩不靠谱的。
┃爱┃C 拉人快拉人。我们的速度最靠谱。
┗━┛D 爱恋快爱恋。我们的热情最靠谱。',
                'cover'        => 'images/aad8fe7f8c9c22b94ca74d8077698bcd.jpeg',
                'follow_count' => 0,
                'feed_count'   => 0,
                'created_at'   => '2020-08-05 14:31:32',
                'updated_at'   => '2022-04-24 20:14:43',
            ],
            1 => [
                'id'          => 2,
                'group_id'    => 3,
                'name'        => '我来帮你拍私房',
                'description' => 'your body is my temple，最美不过身体


欢迎大家分享作品，欢迎模特和摄影师约拍


约拍请去摄影师和模特的报到贴，不发作品或者自己照片的不许单独发帖！ > <

有图有真相好传统 ↖(^ω^)↗


鼓励互免~收费请注明


最后，转载请注明，更欢迎原创作品


最最最后，yp的，就算误伤也不会放过一个！不能玷污拍照这么单纯的事！',
                'cover'        => 'images/46bb7a898ab1ab8f17cdc60acf79b3d8.JPG',
                'follow_count' => 0,
                'feed_count'   => 0,
                'created_at'   => '2020-08-05 14:32:46',
                'updated_at'   => '2022-04-24 20:14:35',
            ],
        ]);
    }
}
