<?php

namespace App\GraphQL\Mutations;

use App\Jobs\ProcessMatchday;
use App\Models\Season;

class SeasonMutation
{
    public function createSeason($root, array $args)
    {
        $seasonId = $args['seasonId'];
        if (!Season::find($seasonId)) {
            Season::create(['seasonId' => $seasonId, 'matchDays' => []]);
        }
        for ($i = 2; $i <= 5; $i++) {
            dispatch(new ProcessMatchday($seasonId, $i));
        }
        return ['seasonId' => $seasonId];
    }
}
