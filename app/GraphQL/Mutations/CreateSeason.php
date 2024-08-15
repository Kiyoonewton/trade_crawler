<?php

namespace App\GraphQL\Mutations;

use App\Jobs\ProcessMatchday;
use App\Models\Season;

class CreateSeason
{
    public function __invoke($_, array $args)
    {
        $seasonId = $args['seasonId'];
        $season = Season::where('seasonId', $seasonId)->first();
        if (!$season) {
            Season::create(['seasonId' => $seasonId]);
        }
        dispatch(new ProcessMatchday($seasonId));
        return ['season_id' => $seasonId];
    }
}
