<?php

namespace App\GraphQL\Mutations;

use App\Models\Season;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMatchday implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $seasonId;
    public $matchDayNumber;

    /**
     * Create a new job instance.
     */
    public function __construct(string $seasonId, int $matchDayNumber)
    {
        $this->$seasonId = $seasonId;
        $this->matchDayNumber = $matchDayNumber;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $apiUrl = "https://vgls.betradar.com/vfl/feeds/?/bet9ja/en/Europe:Berlin/gismo/vfc_stats_round_odds2/vf:season:$this->seasonId/$this->matchDayNumber";
        $response = Http::get($apiUrl);
        if ($response->failed()) {
            throw new \Exception('Cannot fetch data from the api');
        }

        $data = $response->json();
        $season = Season::find($this->seasonId);
        $season->matchDays[] = $data;
        $season->push();
        // return $data;
    }
}
