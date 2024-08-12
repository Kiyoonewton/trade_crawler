<?php

namespace App\Services;

class MatchdayDataClass
{
    protected $matchday;
    protected $matchdayDetails;
    protected $rawDatas;
    protected $pointsTotal;
    protected $highestGoalScored;
    protected $highestGoal;
    protected $doc;
    protected $odds;

    public function __construct()
    {
        $path = storage_path('App/Data/data.json');
        $json = file_get_contents($path);
        $this->matchday = json_decode($json, true);
        $this->doc = $this->matchday["doc"][0];
        $this->odds = collect($this->doc["data"]["odds"]);

        $path2 = storage_path('App/Data/data2.json');
        $json2 = file_get_contents($path2);
        $this->matchdayDetails = json_decode($json2, true);
        $this->rawDatas = $this->matchdayDetails["doc"][0]["data"]["tables"][0]["tablerows"];

        $this->pointsTotal = ["pointsTotal" => $this->rawDatas[0]["pointsTotal"], "teamName" => $this->rawDatas[0]["team"]["name"]];

        $highestGoalScored = new HighestGoalScoredClass($this->rawDatas);
        $this->highestGoal = $highestGoalScored->calculateHighestGoalScored();
    }
    protected function filterMarketByTeam(string $team)
    {
        return collect($this->odds->filter(
            function ($odd) use ($team) {
                return $odd["teams"]["home"]["name"] === $team || $odd["teams"]["away"]["name"] === $team;
            }
        ))->first();
    }
    public function getWinOrDrawMatchday()
    {
        $odd = $this->filterMarketByTeam($this->pointsTotal["teamName"]);
        // return collect($odd["market"]);
        $WinOrDraw = collect(collect($odd["market"])
            ->filter(function ($marketOdd) {
                return $marketOdd["id"] === 1;
            })->values()->first()['outcome'])
            ->map(function ($marketOdd) {
                if ($marketOdd["id"] === "1") {
                    $type = "home";
                } elseif ($marketOdd["id"] === "2") {
                    $type = "draw";
                } else {
                    $type = "away";
                }
                if ($marketOdd['name'] === $this->pointsTotal["teamName"]) {
                    $prediction = $marketOdd['result'] === 1 ? 'win' : 'loss';
                };
                return [
                    "type" => $type,
                    "prediction" => $prediction,
                    "odds" => $marketOdd["odds"],
                    "result" => $marketOdd["result"]
                ];
            })->values()->all();
        $prediction = $WinOrDraw[0]['prediction'];;
        unset($WinOrDraw[0]['prediction']);
        return ["queryUrl" => $this->matchday["queryUrl"],'odds' => ["home" => $odd["teams"]["home"]["name"], "away" => $odd["teams"]["away"]["name"], "market" => ["winordraw" => $WinOrDraw]], 'prediction' => $prediction];
    }
    //     return [
    //         // "index" => $matchdayNumber,
    //         "queryUrl" => $this->matchday["queryUrl"],
    //         "timestamp" => $this->doc["timestamp"],
    //         "outcome" => $team
    //     ];
    // }

    public function getOverOrUnderMatchday()
    {
        $highestGoalTeam = $this->highestGoal['teamName'];
        $odd = $this->filterMarketByTeam($highestGoalTeam);
        $total = collect(collect($odd["market"])->filter(function ($marketOdd) {
            return $marketOdd["id"] === 18 && $marketOdd["specifiers"] === "total=0.5" || $marketOdd["id"] === 18 && $marketOdd["specifiers"] === "total=1.5" || $marketOdd["id"] === 18 && $marketOdd["specifiers"] === "total=2.5" || $marketOdd["id"] === 18 && $marketOdd["specifiers"] === "total=3.5" || $marketOdd["id"] === 18 && $marketOdd["specifiers"] === "total=4.5";
        }))->map(function ($marketType) {
            $key = explode("=",  $marketType["specifiers"]);
            $key = array_map('trim',  $key);

            return ["type" => $key[1], "over" => ["odds" => $marketType["outcome"][0]["odds"], "result" => $marketType["outcome"][0]["result"]], "under" => ["odds" => $marketType["outcome"][1]["odds"], "result" => $marketType["outcome"][1]["result"]]];
        })->values()->all();

        return ["queryUrl" => $this->matchday["queryUrl"],'odd'=>$total, 'prediction'=>$total[1]['over']['result']===1 ? 'over': 'under'];
    }
}
