<?php

namespace App\GraphQL\Mutations;

use App\Jobs\ProcessMatchday;
use App\Models\Season;
use Illuminate\Support\Facades\Http;

class SeasonMutation
{
    public function createSeason($root, array $args)
    {
        $seasonId = $args['seasonId'];
        Season::create(['seasonId' => $seasonId, 'matchDays' => []]);

        for ($i = 1; $i <= 30; $i++) {
            dispatch(new ProcessMatchday($seasonId, $i));
        }
        return ['seasonId' => $seasonId];
    }
}
