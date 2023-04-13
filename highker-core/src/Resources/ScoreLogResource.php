<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class ScoreLogResource extends ResourceCollection
{
    /**
     * @param $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'score' => Auth::user()->score,
            'data'  => $this->collection,
        ];
    }
}
