<?php

namespace App\GraphQL\Mutations;

use App\Models\MatchDay;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class GetSeason implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function create($root, array $args)
    {
        $uuid = $args['seasonId'];
        $apiUrl = "https://vgls.betradar.com/vfl/feeds/?/bet9ja/en/Europe:Berlin/gismo/vfc_stats_round_odds2/vf:season:$uuid/24";

        // Fetch data from the API
        $response = Http::get($apiUrl);
        if ($response->failed()) {
            throw new \Exception('Failed to fetch data from the external API');
        }

        $data = $response->json();

        // Parse the API response and map it to the MatchDay model structure
        $matchDay = new MatchDay();
        $matchDay->queryUrl = $data['queryUrl'];
        $matchDay->doc = $data['doc'];
        $matchDay->save();

        return $matchDay;
    }
}
