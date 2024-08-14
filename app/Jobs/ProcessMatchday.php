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
    public array $data;
    public int $previousMatchDay;
    public function __construct(public string $seasonId, public int $matchdayNumber) {
        $this->data = [];
        $this->previousMatchDay = $matchdayNumber - 1;

    }
    /**
     * Execute the job.
     */
    public function handle()
    {
        $apiUrls = [
            "https://vgls.betradar.com/vfl/feeds/?/bet9ja/en/Europe:Berlin/gismo/vfc_stats_round_odds2/vf:season:$this->seasonId/$this->matchdayNumber",
            "https://vgls.betradar.com/vfl/feeds/?/bet9javirtuals/en/Europe:Berlin/gismo/stats_season_tables/$this->seasonId/1/$this->previousMatchDay"
        ];

        foreach ($apiUrls as $apiUrl) {
            $response = Http::get($apiUrl);
            if ($response->failed()) {
                throw new \Exception('Cannot fetch data from the api');
            }
            array_push($this->data, $response->json());
        }

        $filterMatchdayDataService = new MatchdayDataClass($this->data, $this->matchdayNumber);
        $filteredWinOrDrawData = $filterMatchdayDataService->getWinOrDrawMatchday();
        $filteredOverOrUnder = $filterMatchdayDataService->getOverOrUnderMatchday();
        // if (conditionmet) {
        //     false;
        // };
        // $season = Season::find($this->seasonId);
        // $winordraw = $season->winordraw ?? [];
        // $overorunder = $season->overorunder ?? [];
        $winordraw = $filteredWinOrDrawData;
        // $overorunder = $filteredOverOrUnder;
        // $season->overorunder = $overorunder;
        // $season->winordraw = $winordraw;
        // $season->save();
        return $winordraw;
        // return true;
    }
}
