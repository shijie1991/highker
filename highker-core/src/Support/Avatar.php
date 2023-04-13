<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Support;

/**
 * Class Avatar.
 *
 * @example https://avatars.dicebear.com/styles/micah
 */
class Avatar
{
    private string $baseUri = 'https://avatars.dicebear.com/api/micah';

    public int $size = 300;

    public string $gender = 'unknown'; // male  female

    public array $femaleHair = ['full', 'pixie'];

    public array $maleHair = ['fonze', 'mrT', 'dougFunny', 'mrClean', 'dannyPhantom'];

    public array $baseColor = ['apricot', 'coast', 'topaz', 'lavender', 'sky', 'salmon', 'canary', 'calm', 'azure', 'seashell', 'mellow', 'white'];

    public array $bg = [
        '#5b6eb9',
        '#a78667',
        '#036980',
        '#d11d18',
        '#646464',
        '#528360',
        '#15a0ae',
        '#665f80',
        '#256e96',
        '#008ab8',
        '#662266',
        '#3f5d7d',
        '#616a91',
        '#8a1f1c',
        '#633b5e',
        '#7d84ad',
        '#3f799d',
        '#7f2a23',
        '#607663',
        '#2f4f4f',
        '#a8c3bc',
        '#ff9999',
        '#e34814',
        '#ffbe0a',
        '#bf0000',
        '#ffeead',
        '#ff6f69',
        '#d2ba4b',
        '#ffbaf9',
        '#0d4a78',
        '#b765d7',
        '#2fe0e0',
        '#a9d93c',
        '#8536a5',
        '#60720f',
        '#6d647e',
        '#0001f6',
        '#fe6f5e',
        '#ba9997',
        '#a46050',
        '#366442',
    ];

    /**
     * @param $slug
     *
     * @return string
     */
    public function avatar($slug)
    {
        $url = $slug ? '/'.$slug.'.svg' : '/:seed.svg';

        $hair = $this->gender == 'female' ? $this->femaleHair : $this->maleHair;

        // 头发概率
        $hairProbability = $this->gender == 'female' ? 100 : rand(0, 100);

        // 头发颜色
        $hairColor = $this->baseColor;

        // 眼睛
        $eyes = ['eyes', 'round', 'eyesShadow', 'smiling'];

        // 眼睛阴影颜色
        $eyeShadowColor = $this->baseColor;

        // 眉毛
        $eyebrows = ['up', 'down', 'eyelashesUp', 'eyelashesDown'];

        // 眉毛颜色
        $eyebrowColor = $this->baseColor;

        // 鼻子
        $nose = ['curve', 'pointed', 'tound'];

        // 耳朵
        $ears = ['attached', 'detached'];

        // 衣服
        $shirt = ['open', 'crew', 'collared'];

        // 衣服颜色
        $shirtColor = $this->baseColor;

        // 耳环
        $earrings = ['hoop', 'stud'];

        // 耳环概率
        $earringsProbability = rand(0, 100);

        // 耳环颜色
        $earringColor = $this->baseColor;

        // 眼镜
        $glasses = ['round', 'square'];

        // 眼镜颜色
        $glassesColor = $this->baseColor;

        // 眼镜概率
        $glassesProbability = rand(0, 100);

        // 胡子
        $facialHair = ['beard', 'scruff'];

        // 胡子颜色
        $facialHairColor = $this->baseColor;

        // 胡子概率
        $facialHairProbability = 0;

        // 基本配色
        $baseColor = $this->baseColor;

        // 嘴唇
        $mouth = ['surprised', 'laughing', 'nervous', 'smile', 'pucker', 'smirk'];

        $options = [
            'scale'                 => 100,
            'size'                  => $this->size,
            'b'                     => $this->bg[array_rand($this->bg)],
            'hair'                  => $hair[array_rand($hair)],
            'hairProbability'       => $hairProbability,
            'hairColor'             => $hairColor[array_rand($hairColor)],
            'eyes'                  => $eyes[array_rand($eyes)],
            'eyeShadowColor'        => $eyeShadowColor[array_rand($eyeShadowColor)],
            'eyebrows'              => $eyebrows[array_rand($eyebrows)],
            'eyebrowColor'          => $eyebrowColor[array_rand($eyebrowColor)],
            'nose'                  => $nose[array_rand($nose)],
            'ears'                  => $ears[array_rand($ears)],
            'shirt'                 => $shirt[array_rand($shirt)],
            'shirtColor'            => $shirtColor[array_rand($shirtColor)],
            'earrings'              => $earrings[array_rand($earrings)],
            'earringsProbability'   => $earringsProbability,
            'earringColor'          => $earringColor[array_rand($earringColor)],
            'glasses'               => $glasses[array_rand($glasses)],
            'glassesColor'          => $glassesColor[array_rand($glassesColor)],
            'glassesProbability'    => $glassesProbability,
            'facialHair'            => $facialHair[array_rand($facialHair)],
            'facialHairColor'       => $facialHairColor[array_rand($facialHairColor)],
            'facialHairProbability' => $facialHairProbability,
            'baseColor'             => $baseColor[array_rand($baseColor)],
            'mouth'                 => $mouth[array_rand($mouth)],
        ];

        $params = http_build_query($options);

        $url .= '?'.$params;

        return $this->baseUri.$url;
    }

    /**
     * 设置尺寸.
     *
     * @param $size
     *
     * @return $this
     */
    public function size($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * 设置男性.
     *
     * @return $this
     */
    public function male()
    {
        $this->gender = 'male';

        return $this;
    }

    /**
     * 设置女性.
     *
     * @return $this
     */
    public function female()
    {
        $this->gender = 'female';

        return $this;
    }
}
