<?php

namespace App\PlanningBiblio\Helper;

use App\PlanningBiblio\Helper\BaseHelper;

class StatedWeekAgentsFilterHelper extends BaseHelper
{
    private $site_filter;

    private $service_filter;

    private $statedweek_enabled;

    public function __construct()
    {
        parent::__construct();

        $this->site_filter = $this->config('statedweek_site_filter');
        $this->service_filter = $this->config('statedweek_service_filter');
        $this->statedweek_enabled = 0;
        if (null !== $this->config('statedweek_enabled')
            && $this->config('statedweek_enabled')) {
            $this->statedweek_enabled = 1;
        }
    }

    public function canEnterWorkingHoursFor($agent)
    {
        if ($this->statedweek_enabled == 0) {
            return 1;
        }

        if (!is_array($agent['sites'])) {
            $agent['sites'] = json_decode(html_entity_decode($agent['sites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
        }

        $wrong_site = in_array($this->site_filter, $agent['sites']) ? 1 : 0;
        if ($wrong_site &&
            in_array($agent['service'], $this->config('workinghours_service_filters'))) {
            return 0;
        }

        return 1;
    }
}
