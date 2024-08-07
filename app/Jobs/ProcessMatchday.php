<?php

namespace App\Jobs;

use App\Models\Season;
use App\Services\FilterMatchdayData;
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
    public array $prevPoint = [];
    public function __construct(public string $seasonId, public int $matchdayNumber)
    {
    }
    /**
     * Execute the job.
     */
    public function handle()
    {
        $apiUrl = "https://vgls.betradar.com/vfl/feeds/?/bet9ja/en/Europe:Berlin/gismo/vfc_stats_round_odds2/vf:season:$this->seasonId/$this->matchdayNumber";
        $response = Http::get($apiUrl);
        if ($response->failed()) {
            throw new \Exception('Cannot fetch data from the api');
        }
        $apiUrl2 = "https://vgls.betradar.com/vfl/feeds/?/bet9javirtuals/en/Europe:Berlin/gismo/stats_season_tables/2858982/1/15";
        $data = $response->json();

        $filterMatchdayDataService = new FilterMatchdayData();
        $filtered_data = $filterMatchdayDataService->getFilteredMatchday($data, $this->matchdayNumber);
        $season = Season::find($this->seasonId);
        $matchDays = $season->matchDays ?? [];
        $matchDays[$this->matchdayNumber] = $filtered_data;
        $season->matchDays = $matchDays;
        $season->save();
    }
}
