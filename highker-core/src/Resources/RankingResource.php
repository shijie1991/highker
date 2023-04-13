<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RankingResource extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'ranking' => $this['ranking'],
            'data'    => $this['list'],
        ];
    }
}
