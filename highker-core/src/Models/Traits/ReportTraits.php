<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Models\Traits;

use HighKer\Core\Models\Report;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trait ReportTraits.
 */
trait ReportTraits
{
    public function report(Model $resource, $reason)
    {
        if (!$this->hasReport($resource)) {
            $report = app(Report::class);
            $report->user_id = $this->getKey();
            $report->reason = $reason;
            $report->resources_id = $resource->getKey();
            $report->resources_type = $resource->getMorphClass();

            $this->reports()->save($report);
        }
    }

    public function hasReport(Model $resource)
    {
        return $this->reports()
            ->where('resources_id', $resource->getKey())
            ->where('resources_type', $resource->getMorphClass())
            ->exists()
        ;
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'user_id', $this->getKeyName());
    }
}
