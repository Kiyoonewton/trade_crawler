<?php

namespace App\GraphQL\Mutations;

use App\Models\Season;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;

class CreateMatchDayJob implements ShouldQueue
{
    private $seasonId;
    private $matchDayNumber;

    public function __construct(string $seasonId, int $matchDayNumber)
    {
        $this->seasonId = $seasonId;
        $this->matchDayNumber = $matchDayNumber;
    }
    public function handle()
    {
        $apiUrl = "https://vgls.betradar.com/vfl/feeds/?/bet9ja/en/Europe:Berlin/gismo/vfc_stats_round_odds2/vf:season:$this->seasonId/$this->matchDayNumber";

        $response = Http::get($apiUrl);
        if ($response->failed()) {
            throw new \Exception('Failed to fetch data from the external API');
        }

        $data = $response->json();

        $season = Season::find($this->seasonId);
        $season->matchDays[] = $data;
        $season->save();
    }
}
