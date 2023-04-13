<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Admin\Metrics\User;

use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Metrics\Donut;
use HighKer\Core\Enum\UserGender;
use HighKer\Core\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UserGenderChart extends Donut
{
    protected array $labels = ['男', '女', '未知'];

    /**
     * @return mixed|void
     */
    public function handle(Request $request)
    {
        $date = match ($request->get('option')) {
            '1'     => Carbon::today(),
            '7'     => Carbon::today()->subWeek(),
            '30'    => Carbon::today()->subMonth(),
            '365'   => Carbon::today()->subYear(),
            default => false,
        };
        $result = $this->getData($date);
        $this->withContent($result);
        $chart = collect($result)->map(function ($item) {
            return $item['percent'];
        })->values()->toArray();
        // 图表数据
        $this->withChart($chart);
    }

    /**
     * 设置图表数据.
     *
     * @return $this
     */
    public function withChart(array $data): UserGenderChart
    {
        return $this->chart([
            'series' => $data,
        ]);
    }

    /**
     * 初始化卡片内容.
     */
    protected function init()
    {
        parent::init();

        $this->title('用户性别比例');
        $this->dropdown([
            'all' => '全部',
            '1'   => '今日',
            '7'   => '最近 7 天',
            '30'  => '最近 30 天',
            '365' => '最近 1 年',
        ]);

        $this->chartLabels($this->labels);
        $color = Admin::color();
        $colors = [$color->blue(), $color->pink(), $color->orange()];
        // 设置图表颜色
        $this->chartColors($colors);
    }

    protected function getData($date = false): array
    {
        $userSum = User::query()->when($date, function (Builder $query, $date) {
            return $query->whereDate('created_at', '>=', $date);
        })->count();

        $boySum = User::query()->where('gender', '=', UserGender::MALE)->when($date, function (Builder $query, $date) {
            return $query->whereDate('created_at', '>=', $date);
        })->count();

        $girlSum = User::query()->where('gender', '=', UserGender::FEMALE)->when($date, function (Builder $query, $date) {
            return $query->whereDate('created_at', '>=', $date);
        })->count();

        $unknownSum = $userSum - ($boySum + $girlSum);

        return [
            [
                'sum'     => $boySum,
                'percent' => $boySum ? round($boySum / $userSum * 100, 1) : 0,
            ],
            [
                'sum'     => $girlSum,
                'percent' => $girlSum ? round($girlSum / $userSum * 100, 1) : 0,
            ],
            [
                'sum'     => $unknownSum,
                'percent' => $unknownSum ? round($unknownSum / $userSum * 100, 1) : 0,
            ],
        ];
    }

    /**
     * 设置卡片头部内容.
     *
     * @param $data
     */
    protected function withContent($data): UserGenderChart
    {
        $color = Admin::color();
        $blue = $color->blue();
        $pink = $color->pink();
        $orange = $color->orange();

        $style = 'margin-bottom: 8px';
        $labelWidth = 120;

        return $this->content(
            <<<HTML
                <div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
                    <div style="width: {$labelWidth}px">
                        <i class="fa fa-circle" style="color: {$blue}"></i> {$this->labels[0]} {$data[0]['sum']}
                    </div>
                    <div>{$data[0]['percent']}%</div>
                </div>
                <div class="d-flex pl-1 pr-1" style="{$style}">
                    <div style="width: {$labelWidth}px">
                        <i class="fa fa-circle" style="color: {$pink}"></i> {$this->labels[1]} {$data[1]['sum']}
                    </div>
                    <div>{$data[1]['percent']}%</div>
                </div>
                <div class="d-flex pl-1 pr-1" style="{$style}">
                    <div style="width: {$labelWidth}px">
                        <i class="fa fa-circle" style="color: {$orange}"></i> {$this->labels[2]} {$data[2]['sum']}
                    </div>
                    <div>{$data[2]['percent']}%</div>
                </div>
                HTML
        );
    }
}
