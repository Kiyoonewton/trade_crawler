<?php

namespace App\Jobs;

use App\Models\WinOrDrawMarket;
use App\Services\MatchdayDataClass;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessMatchday implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public array $data;
    public string $firstWin = 'loss';
    public string $secondWin = 'loss';
    public function __construct(public string $seasonId)
    {
        $this->data = [];
    }
    /**
     * Execute the job.
     */
    public function handle()
    {
        for ($i = 26; $i <= 30; $i++) {
            $data = [];
            Log::info("log something", [$i]);
            $apiUrls = [
                "https://vgls.betradar.com/vfl/feeds/?/bet9ja/en/Europe:Berlin/gismo/vfc_stats_round_odds2/vf:season:$this->seasonId/$i",
                "https://vgls.betradar.com/vfl/feeds/?/bet9javirtuals/en/Europe:Berlin/gismo/stats_season_tables/$this->seasonId/1/" . ($i - 1)
            ];

            foreach ($apiUrls as $apiUrl) {
                $response = Http::get($apiUrl);
                if ($response->failed()) {
                    throw new \Exception('Cannot fetch data from the api');
                }
                array_push($data, $response->json());
            }

            if ($this->firstWin === 'loss') {
                $existing = WinOrDrawMarket::where([
                    ['season_id', '=', $this->seasonId],
                    ['market_id', '=', $i],
                ])->first();
                if ($existing) return;
                $filterMatchdayDataService = new MatchdayDataClass($data, $i);
                $filteredWinOrDrawData = $filterMatchdayDataService->getWinOrDrawMatchday();
                $winordraw = $filteredWinOrDrawData;
                $this->firstWin = $filteredWinOrDrawData['outcome'];
                WinOrDrawMarket::create([...$winordraw, 'season_id' => $this->seasonId, 'matchday_id' => $i]);
            }

            // if ($this->secondWin === 'loss') {
            //     $filterMatchdayDataService = new MatchdayDataClass($this->data, $i);
            //     // $filteredOverOrUnder = $filterMatchdayDataService->getOverOrUnderMatchday();
            //     // $overorunder = $filteredOverOrUnder;
            //     // OverOrUnderMarket::create([]);
            //     return $filterMatchdayDataService;
            // }

            if ($this->firstWin === 'win' && $this->secondWin === 'win') {
                break;
            }
        }
    }
}
