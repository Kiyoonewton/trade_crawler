<?php

namespace App\GraphQL\Mutations;

use App\Jobs\ProcessMatchday;
use App\Models\Season;

class SeasonMutation
{
    public function createSeason($_, array $args)
    {
        $seasonId = $args['seasonId'];
        if (!Season::find($seasonId)) {
            Season::create(['seasonId' => $seasonId, 'winordraw' => [], 'overandunder' => []]);
        }
        for ($i = 16; $i <= 30; $i++) {
            $processMatchday = new ProcessMatchday($seasonId, $i);
            $shouldContinue = $processMatchday->handle();
            if (!$shouldContinue) {
                break;
            }
        }
        return ['seasonId' => $seasonId];
    }
}
