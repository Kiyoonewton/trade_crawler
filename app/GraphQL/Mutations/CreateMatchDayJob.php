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

        for ($i = 1; $i <= 3; $i++) {
            $apiUrl = "https://vgls.betradar.com/vfl/feeds/?/bet9ja/en/Europe:Berlin/gismo/vfc_stats_round_odds2/vf:season:$seasonId/$i";
            $response = Http::get($apiUrl);
            if ($response->failed()) {
                throw new \Exception('Cannot fetch data from the api');
            }
            $data = $response->json();
            $season = Season::find($seasonId);
            $matchDays = $season->matchDays ?? [];
            $matchDays[] = $data;
            $season->matchDays = $matchDays;
            $season->save();
        }
        return ['seasonId' => $seasonId];
    }
}
