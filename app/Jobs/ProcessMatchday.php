<?php

namespace App\Jobs;

use App\Models\Season;
use App\Services\MatchdayDataClass;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ProcessMatchday implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public array $datas = [];
    public function __construct(public string $seasonId, public int $matchdayNumber)
    {
    }
    /**
     * Execute the job.
     */
    public function handle(): bool
    {
        $apiUrls = [
            "https://vgls.betradar.com/vfl/feeds/?/bet9ja/en/Europe:Berlin/gismo/vfc_stats_round_odds2/vf:season:$this->seasonId/$this->matchdayNumber", "https://vgls.betradar.com/vfl/feeds/?/bet9javirtuals/en/Europe:Berlin/gismo/stats_season_tables/$this->seasonId/1/15"
        ];

        foreach ($apiUrls as $apiUrl) {
            $response = Http::get($apiUrl);
            if ($response->failed()) {
                throw new \Exception('Cannot fetch data from the api');
            }
            array_push($datas, $response->json());
        }

        $filterMatchdayDataService = new MatchdayDataClass($datas[0], $this->matchdayNumber, $datas[1]);
        $filteredWinOrDrawData = $filterMatchdayDataService->getWinOrDrawMatchday();
        $filteredOverOrUnder = $filterMatchdayDataService->getOverOrUnderMatchday();
        // if (conditionmet) {
        //     false;
        // };
        $season = Season::find($this->seasonId);
        $winordraw = $season->winordraw ?? [];
        $overorunder = $season->overorunder ?? [];
        $winordraw[$this->matchdayNumber] = $filteredWinOrDrawData;
        $overorunder[$this->matchdayNumber] = $filteredOverOrUnder;
        $season->overorunder = $overorunder;
        $season->winordraw = $winordraw;
        $season->save();
        return true;
    }
}
