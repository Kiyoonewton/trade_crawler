<?php

namespace App\GraphQL\Mutations;

use App\Jobs\ProcessMatchday;
use App\Models\Season;

class CreateSeason
{
    public function __invoke($_, array $args)
    {
        $seasonId = $args['seasonId'];
        // $season = Season::where('seasonId', $seasonId)->first();
        // if (!$season) {
        //     $season = Season::create(['seasonId' => $seasonId]);
        // }
        $bet = [];
        for ($i = 26; $i <= 30; $i++) {
            $processMatchday = new ProcessMatchday($seasonId, $i);
            $shouldNotContinue = $processMatchday->handle();
            array_push($bet, $processMatchday->handle());
            if ($shouldNotContinue['prediction'] === "win") {
                break;
            }
        }
        return $bet;
        // return ['seasonId' => $seasonId];
    }
}
