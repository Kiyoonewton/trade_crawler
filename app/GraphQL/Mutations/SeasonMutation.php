<?php

namespace App\GraphQL\Mutations;

use App\Models\Season;
use Illuminate\Support\Facades\Bus;

class SeasonMutation
{
    public function createSeason($root, array $args)
    {
        $season = Season::create(['seasonId' => $args['seasonId'], 'matchDays' => []]);

        for ($i = 1; $i <= 3; $i++) {
            Bus::chain([new CreateMatchDayJob($args['seasonId'], $i)])->dispatch();
        }

        return $season;
    }
}
